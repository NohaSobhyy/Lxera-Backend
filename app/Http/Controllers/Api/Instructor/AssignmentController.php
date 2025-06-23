<?php

namespace App\Http\Controllers\Api\Instructor;

use App\Http\Controllers\Api\Instructor\traits\LearningPageAssignmentTrait;
use App\Http\Controllers\Controller;
use App\Http\Resources\WebinarAssignmentHistoryResource;
use App\Http\Resources\WebinarAssignmentResource;
use App\Models\Api\Organization;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Sale;
use App\Models\Api\WebinarAssignment;
use App\Models\Api\WebinarAssignmentHistory;
use App\Models\File;
use App\Models\Translation\WebinarAssignmentTranslation;
use App\Models\Webinar;
use App\Models\WebinarAssignmentHistoryMessage;
use App\Models\WebinarChapterItem;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{
    use LearningPageAssignmentTrait;

    public function index(Request $request)
    {
        if (!getFeaturesSettings('webinar_assignment_status')) {
            abort(403);
        }

        $user = auth()->user();

        if (!$user->isOrganization() and !$user->isTeacher()) {
            abort(404);
        }

        $query = WebinarAssignment::where('creator_id', $user->id)
            ->orWhereHas('webinar', function ($query) use ($user) {
                $query->where('teacher_id', $user->id)
                    ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
                        $q->where('teacher_id', $user->id);
                    });
            });

        $courseAssignmentsCount = deepClone($query)->count();

        $pendingReviewCount = deepClone($query)->whereHas('instructorAssignmentHistories', function ($query) use ($user) {
            // $query->where('instructor_id', $user->id);
            $query->where('status', WebinarAssignmentHistory::$pending);
        })->count();

        $passedCount = deepClone($query)->whereHas('instructorAssignmentHistories', function ($query) use ($user) {
            // $query->where('instructor_id', $user->id);
            $query->where('status', WebinarAssignmentHistory::$passed);
        })->count();

        $failedCount = deepClone($query)->whereHas('instructorAssignmentHistories', function ($query) use ($user) {
            // $query->where('instructor_id', $user->id);
            $query->where('status', WebinarAssignmentHistory::$notPassed);
        })->count();

        $assignments = $query->with([
            'webinar',
            // 'instructorAssignmentHistories' => function ($query) use ($user) {
            //     $query->where('instructor_id', $user->id);
            // },
        ])->orderBy('created_at', 'desc')
            ->get();

        foreach ($assignments as &$assignment) {
            $grades = $assignment->instructorAssignmentHistories->filter(function ($item) {
                return !is_null($item->grade);
            });

            $historyIds = $assignment->instructorAssignmentHistories->pluck('id')->toArray();

            $assignment->min_grade = count($grades) ? $grades->min('grade') : null;
            $assignment->average_grade = count($grades) ? $grades->avg('grade') : null;
            $assignment->submissions = WebinarAssignmentHistoryMessage::whereIn('assignment_history_id', $historyIds)
                ->where('sender_id', '!=', $user->id)
                ->count();

            $assignment->pendingCount = $assignment->instructorAssignmentHistories->where('status', WebinarAssignmentHistory::$pending)->count();
            $assignment->passedCount = $assignment->instructorAssignmentHistories->where('status', WebinarAssignmentHistory::$passed)->count();
            $assignment->failedCount = $assignment->instructorAssignmentHistories->where('status', WebinarAssignmentHistory::$notPassed)->count();
        }

        $data = [
            'pageTitle' => trans('update.my_courses_assignments'),
            'assignments' => $assignments,
            'courseAssignmentsCount' => $courseAssignmentsCount,
            'pendingReviewCount' => $pendingReviewCount,
            'passedCount' => $passedCount,
            'failedCount' => $failedCount,
        ];

        return apiResponse2(
            1,
            'retrieved',
            trans('api.public.retrieved'),
            $data
        );
    }

    public function students(Request $request)
    {
        if (!getFeaturesSettings('webinar_assignment_status')) {
            abort(403);
        }

        $user = apiAuth();

        $assignment = WebinarAssignment::where('creator_id', $user->id)
            // ->where('creator_id', $user->id)
            ->with([
                'webinar',
            ])
            ->first();

        if (!empty($assignment)) {
            $webinar = $assignment->webinar;

            $query = $assignment->assignmentHistory()
                ->where('instructor_id', $user->id)
                ->where('student_id', '!=', $user->id)
                ->with([
                    'student'
                ]);

            $courseAssignmentsCount = WebinarAssignment::where('creator_id', $user->id)
                ->where('webinar_id', $webinar->id)
                ->count();

            $pendingReviewCount = deepClone($query)->where('status', WebinarAssignmentHistory::$pending)->count();
            $passedCount = deepClone($query)->where('status', WebinarAssignmentHistory::$passed)->count();
            $failedCount = deepClone($query)->where('status', WebinarAssignmentHistory::$notPassed)->count();


            $histories = $query->orderBy('created_at', 'desc')
                ->get();
            //  dd($histories);
            foreach ($histories as &$history) {
                $history->usedAttemptsCount = 0;

                $sale = Sale::where('buyer_id', $history->student_id)
                    ->where('webinar_id', $assignment->webinar_id)
                    ->whereNull('refund_at')
                    ->first();

                if (!empty($sale)) {
                    $history->purchase_date = $sale->created_at;
                }

                if (!empty($history) and count($history->messages)) {
                    try {
                        $history->last_submission = $history->messages->first()->created_at;
                        $history->first_submission = $history->messages->last()->created_at;
                        $history->usedAttemptsCount = $history->messages->count();
                    } catch (\Exception $exception) {
                    }
                }
            }
            $resource = WebinarAssignmentHistoryResource::collection($histories);
            //  dd($resource->groupBy('id')) ;
            //  $resource=$resource->groupBy('student_id')

            $data = [
                'pageTitle' => trans('update.students_assignments'),
                'assignment' => $assignment,
                'histories' => $histories,

                'webinar' => $webinar,
                'courseAssignmentsCount' => $courseAssignmentsCount,
                'pendingReviewCount' => $pendingReviewCount,
                'passedCount' => $passedCount,
                'failedCount' => $failedCount,
            ];

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), [
                'assignment_histories' => $resource,
                'count' => $courseAssignmentsCount,
                'pending_count' => $pendingReviewCount,
                'passed_count' => $passedCount,
                'failed_count' => $failedCount,

            ]);

            //  return view('web.default.panel.assignments.students', $data);
        }

        abort(404);
    }

    public function submmision($url_name, Request $request, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }
        if (!getFeaturesSettings('webinar_assignment_status')) {
            abort(403);
        }

        $user = auth()->user();

        if (!$user->isOrganization() and !$user->isTeacher()) {
            abort(404);
        }

        $assignment = WebinarAssignment::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('creator_id', $user->id)
                    ->orWhereHas('webinar', function ($query) use ($user) {
                        $query->where('teacher_id', $user->id)
                            ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
                                $q->where('teacher_id', $user->id);
                            });
                    });
            })
            ->with([
                'webinar',
            ])
            ->first();
        if (!empty($assignment)) {
            $webinar = $assignment->webinar;

            $query = $assignment->instructorAssignmentHistories()
                // ->where('instructor_id', $user->id)
                ->where('student_id', '!=', $user->id)
                ->with([
                    'student'
                ]);

            // $courseAssignmentsCount = WebinarAssignment::where('creator_id', $user->id)
            //     ->orWhereHas('webinar', function ($query) use ($user) {
            //         $query->where('teacher_id', $user->id)
            //             ->orWhereHas('PartnerTeachers', function ($q) use ($user) {
            //                 $q->where('teacher_id', $user->id);
            //             });
            //     })
            //     ->where('webinar_id', $webinar->id)
            //     ->count();

            $courseAssignmentsCount = deepClone($query)->count();

            $pendingReviewCount = deepClone($query)->where('status', WebinarAssignmentHistory::$pending)->count();
            $passedCount = deepClone($query)->where('status', WebinarAssignmentHistory::$passed)->count();
            $failedCount = deepClone($query)->where('status', WebinarAssignmentHistory::$notPassed)->count();

            $query = $this->handleAssignmentStudentsFilters($request, $query);

            $histories = $query->orderBy('created_at', 'desc')
                ->get();

            foreach ($histories as &$history) {
                $history->usedAttemptsCount = 0;

                $sale = Sale::where('buyer_id', $history->student_id)
                    ->where('webinar_id', $assignment->webinar_id)
                    ->whereNull('refund_at')
                    ->first();

                if (!empty($sale)) {
                    $history->purchase_date = $sale->created_at;
                }

                if (!empty($history) and count($history->messages)) {
                    try {
                        $history->last_submission = $history->messages->first()->created_at;
                        $history->first_submission = $history->messages->last()->created_at;
                        $history->usedAttemptsCount = $history->messages->count();
                    } catch (\Exception $exception) {
                    }
                }
            }

            $studentsIds = Sale::where('webinar_id', $webinar->id)
                ->whereNull('refund_at')
                ->pluck('buyer_id')
                ->toArray();

            $students = User::select('id', 'full_name')
                ->whereIn('id', $studentsIds)
                ->get();

            $data = [
                'pageTitle' => trans('update.students_assignments'),
                'assignment' => $assignment,
                'histories' => $histories,
                'students' => $students,
                'webinar' => $webinar,
                'courseAssignmentsCount' => $courseAssignmentsCount,
                'pendingReviewCount' => $pendingReviewCount,
                'passedCount' => $passedCount,
                'failedCount' => $failedCount,
            ];

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $data);
        }

        abort(404);
    }

    private function handleAssignmentStudentsFilters(Request $request, $query)
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $studentId = $request->get('student_id');
        $status = $request->get('status');

        // $from and $to
        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($studentId)) {
            $query->where('student_id', $studentId);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        return $query;
    }

    public function setGrade($url_name, Request $request, $historyId)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }
        $user = apiAuth();
        validateParam($request->all(), [
            'grade' => 'required|integer',
        ]);

        $assignmentHistory = WebinarAssignmentHistory::where('id', $historyId)->first();
        abort_unless($assignmentHistory, 404);
        $assignment = $assignmentHistory->assignment;
        $webinar = $assignment->webinar;
        $data = $request->all();
        $grade = $data['grade'];

        $status = WebinarAssignmentHistory::$passed;

        if ($grade < $assignment->pass_grade) {
            $status = WebinarAssignmentHistory::$notPassed;
        }

        $assignmentHistory->update([
            'status' => $status,
            'grade' => $grade
        ]);

        if ($status == WebinarAssignmentHistory::$passed) {
            $buyStoreReward = RewardAccounting::calculateScore(Reward::PASS_ASSIGNMENT);
            RewardAccounting::makeRewardAccounting($assignmentHistory->student_id, $buyStoreReward, Reward::PASS_ASSIGNMENT, $assignment->id);
        }

        $notifyOptions = [
            '[instructor.name]' => $assignmentHistory->instructor->full_name,
            '[c.title]' => $webinar->title,
            '[student.name]' => $assignmentHistory->student->full_name,
            '[assignment_grade]' => $assignmentHistory->grade,
        ];

        sendNotification('instructor_set_grade', $notifyOptions, $assignmentHistory->student_id);

        return apiResponse2(1, 'stored', trans('api.public.stored'));
    }

    public function storeMessage($url_name, Request $request, $assignmentId, $historyId)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }
        $user = auth()->user();

        $assignment = WebinarAssignment::where('id', $assignmentId)->first();

        if (!empty($assignment)) {
            $webinar = $assignment->webinar;

            if (!empty($webinar) and $webinar->checkUserHasBought($user)) {
                $studentId = $request->get('student_id');
                $assignmentHistory = $this->getAssignmentHistory($webinar, $assignment, $user, $studentId);

                if (!empty($assignmentHistory) and $historyId == $assignmentHistory->id) {

                    if ($user->id != $assignment->creator_id) {
                        $submissionTimes = $assignmentHistory->messages
                            ->where('sender_id', $user->id)
                            ->whereNotNull('file_path')
                            ->count();
                        $deadline = $this->getAssignmentDeadline($assignment, $user);

                        if (!$deadline or (!empty($assignment->attempts) and $submissionTimes >= $assignment->attempts)) {
                            $toastData = [
                                'title' => !$deadline ? trans('update.assignment_deadline_error_title') : trans('update.assignment_submission_error_title'),
                                'msg' => !$deadline ? trans('update.assignment_deadline_error_desc') : trans('update.assignment_submission_error_desc'),
                            ];

                            return response([
                                'code' => 401,
                                'errors' => $toastData,
                            ]);
                        }
                    }

                    $data = $request->all();

                    $rules = [
                        'file_title' => 'required|max:255',
                        // 'file_path' => 'required|mimes:psd,rar,png,jpg,jpeg,doc,docx,pdf,ai,indd',
                    ];


                    $validator = Validator::make($data, $rules);

                    $path = public_path($data['file_path']);
                    if ($request->hasFile('file_path')) {
                        $file = $request->file('file_path');

                        // Continue with file validation and upload
                        $rules = [
                            'file_path' => 'required|mimes:psd,rar,png,jpg,jpeg,doc,docx,pdf,ai,indd',
                        ];
                        $validator = Validator::make($data, $rules);

                        if ($validator->fails()) {
                            return response([
                                'code' => 422,
                                'errors' => $validator->errors(),
                            ], 422);
                        }
                    }


                    if ($validator->fails()) {


                        return response([
                            'code' => 422,
                            'errors' => $validator->errors(),
                        ], 422);
                    }

                    if (!File::exists($path)) {
                        return response([
                            'code' => 422,
                            'errors' => [
                                'file_path' => ['ملف غير صحيح']
                            ],
                        ], 422);
                    }

                    WebinarAssignmentHistoryMessage::create([
                        'assignment_history_id' => $assignmentHistory->id,
                        'sender_id' => $user->id,
                        'message' => !empty($data['message']) ? $data['message'] : null,
                        'file_title' => $data['file_title'] ?? null,
                        'file_path' => $data['file_path'] ?? null,
                        'created_at' => time(),
                    ]);

                    if ($assignmentHistory->status == WebinarAssignmentHistory::$notSubmitted) {
                        $assignmentHistory->update([
                            'status' => WebinarAssignmentHistory::$pending
                        ]);
                    }

                    $notifyOptions = [
                        '[instructor.name]' => $assignmentHistory->instructor->full_name,
                        '[c.title]' => $webinar->title,
                        '[student.name]' => $assignmentHistory->student->full_name,
                        //'[assignment_grade]' => $assignmentHistory->grade,
                    ];

                    if ($user->id == $assignment->creator_id) {
                        sendNotification('instructor_send_message', $notifyOptions, $assignmentHistory->student_id);
                    } else {
                        sendNotification('student_send_message', $notifyOptions, $assignmentHistory->instructor_id);
                    }

                    return response()->json([
                        'status' => 'success',
                        'msg' => 'Message send successfully'
                    ], 200);
                }
            }
        }

        abort(403);
    }

    public function store($url_name, Request $request)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }
        $user = auth()->user();
        $data = $request->all();

        $rules = [
            'webinar_id' => 'required',
            'chapter_id' => 'required',
            'title' => 'required|max:255',
            'description' => 'required',
            'grade' => 'required|integer',
            'pass_grade' => 'required|integer',
            'deadline' => 'required|date',
            'attempts' => 'nullable|integer',
            'status' => 'nullable|in:active,inactive',
            'check_previous_parts' => 'nullable|boolean',
            'access_after_day' => 'nullable|integer',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $webinar = Webinar::find($data['webinar_id']);

        if (!empty($webinar) and $webinar->canAccess($user)) {

            // if (!empty($data['sequence_content']) and $data['sequence_content'] == 'on') {
            $data['check_previous_parts'] = (!empty($data['check_previous_parts']) and $data['check_previous_parts'] == 'on');
            $data['access_after_day'] = !empty($data['access_after_day']) ? strtotime($data['access_after_day']) : null;
            // } else {
            //     $data['check_previous_parts'] = false;
            //     $data['access_after_day'] = null;
            // }

            $assignment = WebinarAssignment::create([
                'creator_id' => $user->id,
                'webinar_id' => $data['webinar_id'],
                'chapter_id' => $data['chapter_id'],
                'grade' => $data['grade'] ?? null,
                'pass_grade' => $data['pass_grade'] ?? null,
                'deadline' => $data['deadline'] ? strtotime($data['deadline']) : null,
                'attempts' => $data['attempts'] ?? null,
                'check_previous_parts' => $data['check_previous_parts'],
                'access_after_day' => $data['access_after_day'],
                'status' => (!empty($data['status']) and $data['status'] == 'on') ? File::$Active : File::$Inactive,
                'created_at' => time(),
            ]);

            if (!empty($assignment)) {
                WebinarAssignmentTranslation::updateOrCreate([
                    'webinar_assignment_id' => $assignment->id,
                    'locale' => mb_strtolower($data['locale']),
                ], [
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]);

                // $this->handleAttachments($data['attachments'], $user->id, $assignment->id);

                WebinarChapterItem::makeItem($assignment->creator_id, $assignment->chapter_id, $assignment->id, WebinarChapterItem::$chapterAssignment);
            }

            return response()->json([
                'success' => true,
                'message' => 'Assignment added successfully',
                'assignment' => $data
            ], 200);
        }

        abort(403);
    }

    public function update($url_name, Request $request, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }
        $user = auth()->user();
        $data = $request->all();

        $rules = [
            'webinar_id' => 'required',
            'chapter_id' => 'required',
            'title' => 'sometimes|max:255',
            'description' => 'sometimes',
            'grade' => 'sometimes|integer',
            'pass_grade' => 'sometimes|integer',
            'deadline' => 'sometimes|date',
            'attempts' => 'sometimes|integer',
            'status' => 'sometimes|in:active,inactive',
            'check_previous_parts' => 'sometimes|boolean',
            'access_after_day' => 'sometimes|integer',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $webinar = Webinar::find($data['webinar_id']);

        if (!empty($webinar) and $webinar->canAccess($user)) {
            // if (!empty($data['sequence_content']) and $data['sequence_content'] == 'on') {
            $data['check_previous_parts'] = (!empty($data['check_previous_parts']) and $data['check_previous_parts'] == 'on');
            $data['access_after_day'] = !empty($data['access_after_day']) ? strtotime($data['access_after_day']) : null;
            // } else {
            //     $data['check_previous_parts'] = false;
            //     $data['access_after_day'] = null;
            // }

            $assignment = WebinarAssignment::where('id', $id)
                ->where(function ($query) use ($user, $webinar) {
                    $query->where('creator_id', $user->id);
                    $query->orWhere('webinar_id', $webinar->id);
                })
                ->first();

            if (!empty($assignment)) {
                $changeChapter = ($data['chapter_id'] != $assignment->chapter_id);
                $oldChapterId = $assignment->chapter_id;

                $assignment->update([
                    'chapter_id' => $data['chapter_id'],
                    'grade' => $data['grade'] ?? $assignment->grade,
                    'pass_grade' => $data['pass_grade'] ?? $assignment->pass_grade,
                    'deadline' => $data['deadline'] ?? $assignment->deadline,
                    'attempts' => $data['attempts'] ?? $assignment->attempts,
                    'check_previous_parts' => $data['check_previous_parts'] ?? $assignment->check_previous_parts,
                    'access_after_day' => $data['access_after_day'] ?? $assignment->access_after_day,
                    'status' => $data['status'] ?? $assignment->status,
                ]);

                if ($changeChapter) {
                    WebinarChapterItem::changeChapter($assignment->creator_id, $oldChapterId, $assignment->chapter_id, $assignment->id, WebinarChapterItem::$chapterAssignment);
                }

                WebinarAssignmentTranslation::updateOrCreate([
                    'webinar_assignment_id' => $assignment->id,
                    'locale' => $data['locale'] ?? 'ar',
                ], [
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]);

                // $this->handleAttachments($data['attachments'], $assignment->creator_id, $assignment->id);

                return response()->json([
                    'success' => true,
                    'message' => 'Assignment updated successfully',
                    'assignment' => $assignment
                ], 200);
            }
        }

        abort(403);
    }

    public function destroy($url_name, $id)
    {
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Organization not found'], 404);
        }
        $user = auth()->user();

        $assignments = WebinarAssignment::where('id', $id)->first();

        if (!empty($assignments)) {
            $webinar = Webinar::query()->find($assignments->webinar_id);

            if ($assignments->creator_id == $user->id or (!empty($webinar) and $webinar->canAccess($user))) {

                WebinarChapterItem::where('user_id', $assignments->creator_id)
                    ->where('item_id', $assignments->id)
                    ->where('type', WebinarChapterItem::$chapterAssignment)
                    ->delete();

                $assignments->delete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Assignment deleted successfully',
        ], 200);
    }
}
