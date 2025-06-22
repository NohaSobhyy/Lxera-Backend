<?php

namespace App\Http\Controllers\Api\Instructor;

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
use App\Models\WebinarChapterItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $user = apiAuth();
        if (!$user->isTeacher()) {
            abort(404);
        }

        $query = WebinarAssignment::where('creator_id', $user->id);

        $courseAssignmentsCount = $query->count();

        $pendingReviewCount = (clone $query)->whereHas('instructorAssignmentHistories', function ($q) use ($user) {
            $q->where('instructor_id', $user->id)
                ->where('status', WebinarAssignmentHistory::$pending);
        })->count();

        $passedCount = (clone $query)->whereHas('instructorAssignmentHistories', function ($q) use ($user) {
            $q->where('instructor_id', $user->id)
                ->where('status', WebinarAssignmentHistory::$passed);
        })->count();

        $failedCount = (clone $query)->whereHas('instructorAssignmentHistories', function ($q) use ($user) {
            $q->where('instructor_id', $user->id)
                ->where('status', WebinarAssignmentHistory::$notPassed);
        })->count();

        $assignments = (clone $query)->with([
            'webinar',
            'instructorAssignmentHistories' => function ($q) use ($user) {
                $q->where('instructor_id', $user->id);
            },
        ])->orderBy('created_at', 'desc')->get();

        return apiResponse2(
            1,
            'retrieved',
            trans('api.public.retrieved'),
            [
                'course_assignments_count' => $courseAssignmentsCount,
                'pending_reviews_count' => $pendingReviewCount,
                'passed_count' => $passedCount,
                'failed_count' => $failedCount,
                'assignments' => WebinarAssignmentResource::collection($assignments),
            ]
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

    public function submmision(Request $request, $id)
    {
        if (!getFeaturesSettings('webinar_assignment_status')) {
            abort(403);
        }

        $user = apiAuth();

        $assignment = WebinarAssignment::where('creator_id', $user->id)
            ->where('id', $id)
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

            return apiResponse2(1, 'retrieved', trans('api.public.retrieved'), $resource);

            //  return view('web.default.panel.assignments.students', $data);
        }

        abort(404);
    }

    public function setGrade(Request $request, $historyId)
    {
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
                    'locale' => $data['locale'] ?? $assignment->locale,
                ], [
                    'title' => $data['title'],
                    'description' => $data['description'],
                ]);

                $this->handleAttachments($data['attachments'], $assignment->creator_id, $assignment->id);

                return response()->json([
                    'success' => true,
                    'message' => 'Assignment updated successfully',
                    'assignment' => $assignment
                ], 200);
            }
        }

        abort(403);
    }

    public function destroy(Request $request, $id)
    {
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
            'code' => 200
        ], 200);
    }
}
