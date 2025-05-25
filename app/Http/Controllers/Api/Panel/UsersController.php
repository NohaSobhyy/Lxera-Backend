<?php

namespace App\Http\Controllers\Api\Panel;

use App\Models\Sale;
use App\Bitwise\UserLevelOfTraining;
use App\Http\Controllers\Api\Controller;
use App\Http\Resources\InstallmentResource;
use App\Models\Category;
use App\Models\Newsletter;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\UserMeta;
use App\Models\Follow;
use App\Models\UserZoomApi;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Api\UploadFileManager;
use App\Mixins\Installment\InstallmentPlans;
use App\Models\Meeting;
use App\Models\ReserveMeeting;
use App\Models\Role;
use App\StudentRequirement;
use App\BundleStudent;
use App\Http\Resources\ActiveBundleResource;
use App\Models\Group;
use App\Http\Controllers\Admin\SaleController;
use App\Models\Api\Organization;
use App\Models\Api\Webinar;
use App\Models\Enrollment;
use App\Models\BecomeInstructor;
use App\Models\ForumTopic;
use App\Models\Region;
use App\Models\UserBank;
use App\Models\Badge;
use App\Student;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{
    public function setting()
    {
        $user = apiAuth();
        return apiResponse2(
            1,
            'retrieved',
            trans('api.public.retrieved'),
            [
                'user' => $user->details
            ]
        );
    }

    public function updateImages(Request $request)
    {
        $user = apiAuth();
        if ($request->file('profile_image')) {

            $profileImage = $this->createImage($user, $request->file('profile_image'));
            $user->update([
                'avatar' => $profileImage
            ]);
        }

        if ($request->file('identity_scan')) {

            $storage = new UploadFileManager($request->file('identity_scan'));

            $user->update([
                'identity_scan' => $storage->storage_path,
            ]);
        }

        if ($request->file('certificate')) {

            $storage = new UploadFileManager($request->file('certificate'));

            $user->update([
                'certificate' => $storage->storage_path,
            ]);
        }

        return apiResponse2(1, 'updated', trans('api.public.updated'));
    }

    public function update(Request $request, $id)
    {
        Log::info('Update request received:', $request->all());

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->full_name = $request->input('full_name'); // Ensure this field is in your request
        $user->save();

        return response()->json(['message' => 'User updated successfully'], 200);
    }




    private function handleNewsletter($email, $user_id, $joinNewsletter)
    {
        $check = Newsletter::where('email', $email)->first();
        if ($joinNewsletter) {
            if (empty($check)) {
                Newsletter::create([
                    'user_id' => $user_id,
                    'email' => $email,
                    'created_at' => time()
                ]);
            } else {
                $check->update([
                    'user_id' => $user_id,
                ]);
            }

            $newsletterReward = RewardAccounting::calculateScore(Reward::NEWSLETTERS);
            RewardAccounting::makeRewardAccounting($user_id, $newsletterReward, Reward::NEWSLETTERS, $user_id, true);
        } elseif (!empty($check)) {
            $reward = RewardAccounting::where('user_id', $user_id)
                ->where('item_id', $user_id)
                ->where('type', Reward::NEWSLETTERS)
                ->where('status', RewardAccounting::ADDICTION)
                ->first();

            if (!empty($reward)) {
                $reward->delete();
            }

            $check->delete();
        }
    }

    public function updatePassword(Request $request)
    {
        validateParam($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:6',
        ]);

        $user = apiAuth();
        if (Hash::check($request->input('current_password'), $user->password)) {
            $user->update([
                'password' => User::generatePassword($request->input('new_password'))
            ]);
            $token = auth('api')->refresh();

            return apiResponse2(1, 'updated', trans('api.public.updated'), [
                'token' => $token
            ]);
        }
        return apiResponse2(0, 'incorrect', trans('api.public.profile_setting.incorrect'));
    }

    private function updateMeta(array $metaFields, Request $request)
    {
        $user = apiAuth();

        foreach ($metaFields as $name) {
            $value = $request->input($name); // safer than global helper
            $checkMeta = UserMeta::where('user_id', $user->id)
                ->where('name', $name)
                ->first();

            if (!empty($checkMeta)) {
                if (!is_null($value)) {
                    $checkMeta->update(['value' => $value]);
                } else {
                    $checkMeta->delete();
                }
            } elseif (!is_null($value)) {
                UserMeta::create([
                    'user_id' => $user->id,
                    'name' => $name,
                    'value' => $value,
                ]);
            }
        }
    }

    public function followToggle(Request $request, $id)
    {
        // dd('ff') ;
        $authUser = apiAuth();
        validateParam($request->all(), [
            'status' => 'required|boolean'
        ]);

        $status = $request->input('status');

        $user = User::where('id', $id)->first();
        if (!$user) {
            abort(404);
        }
        $followStatus = false;
        $follow = Follow::where('follower', $authUser->id)
            ->where('user_id', $user->id)
            ->first();

        if ($status) {

            if (empty($follow)) {
                Follow::create([
                    'follower' => $authUser->id,
                    'user_id' => $user->id,
                    'status' => Follow::$accepted,
                ]);

                $followStatus = true;
            }
            return apiResponse2(1, 'followed', trans('api.user.followed'));
        }

        if (!empty($follow)) {

            $follow->delete();
            return apiResponse2(1, 'unfollowed', trans('api.user.unfollowed'));
        }

        return apiResponse2(0, 'not_followed', trans('api.user.not_followed'));
    }

    public function createImage($user, $img)
    {
        $folderPath = "/" . $user->id . '/avatar';

        //     $image_parts = explode(";base64,", $img);
        //   $image_type_aux = explode("image/", $image_parts[0]);
        //   $image_type = $image_type_aux[1];
        //  $image_base64 = base64_decode($image_parts[1]);
        // $file = uniqid() . '.' . $image_type;

        $file = uniqid() . '.' . $img->getClientOriginalExtension();
        $storage_path = $img->storeAs($folderPath, $file);
        return 'store/' . $storage_path;

        //    Storage::disk('public')->put($folderPath . $file, $img);

        //  return Storage::disk('public')->url($folderPath . $file);
    }

    // requirement index
    public function requirementIndex($step = 1)
    {
        $user = auth("api")->user();

        $student = $user->Student;

        if (!$student) {
            return apiResponse2(0, 'not_student', "you need to apply to diploma first");
        }

        $studentBundles = BundleStudent::where('student_id', $student->id)->get()->reverse();

        /* Installments */
        $bundleInstallments = [];

        foreach ($studentBundles as $studentBundle) {
            $hasBought = $studentBundle->bundle->checkUserHasBought($user);
            $boughtInstallment = $studentBundle->bundle->getInstallmentOrder();
            $canSale = ($studentBundle->bundle->canSale() && !$hasBought);
            $studentBundle->bundle->title = $studentBundle->bundle->title;
            $studentBundle->requirement = [
                "status" => !empty($studentBundle->studentRequirement) ? $studentBundle->studentRequirement->status : null,
                "upload_link" => "/panel/bundles/$studentBundle->id/requirements"
            ];

            // Check if the bundle meets the conditions
            if ($canSale && !empty($studentBundle->bundle->price) && $studentBundle->bundle->price > 0 && getInstallmentsSettings('status') && (empty($user) || $user->enable_installments)) {
                $installmentPlans = new InstallmentPlans($user);
                $installment = $installmentPlans->getPlans('bundles', $studentBundle->bundle->id, $studentBundle->bundle->type, $studentBundle->bundle->category_id, $studentBundle->bundle->teacher_id)->last() ?? null;

                $bundleInstallments[] = [
                    'studentBundle' => $studentBundle,
                    'installment' => InstallmentResource::make($installment),
                    'hasBought' => $hasBought,
                    'boughtInstallment' => $boughtInstallment,
                    "cache_payment_url" => "/panel/bundles/purchase",
                    "installment_payment_url" => "/panel/bundles/purchase/$installment->id"
                ];
            } else {

                $bundleInstallments[] = [
                    'studentBundle' => $studentBundle,
                    'installment' => null,
                    'hasBought' => $hasBought,
                    'boughtInstallment' => $boughtInstallment,
                    "cache_payment_url" => "/panel/bundles/purchase",
                    "installment_payment_url" => null
                ];
            }
        }
        return apiResponse2(1, 'requirements_details', "all data retireved successfully", $bundleInstallments ?? null);
    }

    // create requirement function
    public function createRequirement($studentBundleId)
    {
        $user = auth("api")->user();

        $student = $user->Student;

        $studentBundle = BundleStudent::find($studentBundleId);

        if (!$student) {
            return apiResponse2(0, 'not_student', "you need to apply to diploma first");
        }

        if (!$studentBundle) {
            return apiResponse2(0, "doesn't match", "this is not your bundle url");
        }

        $data = [
            "user_code" => $user->user_code,
            'requirementUploaded' => false,
            'requirementStatus' => StudentRequirement::pending,
            'bundle' => ActiveBundleResource::make($studentBundle->bundle),
            'studentBundleId' => $studentBundleId,
            "requirmentsFile" => "https://anasacademy.uk/wp-content/uploads/2023/12/نموذج-عقد-اتفاقية-التحاق-متدربـ-النسخة-الاخيرة.pdf"
        ];

        $studentRequirments = $studentBundle->studentRequirement;

        if ($studentRequirments) {

            $data["requirementUploaded"] = true;
            $data["requirementStatus"] = $studentRequirments->status;
        }

        return apiResponse2(0, 'create_requirement', "retieve what you need to upload requirements", $data);
    }

    // store requirements
    public function storeRequirement(Request $request, $studentBundleId)
    {
        $rules = [
            'user_code' => 'required|string',
            'program' => 'required|string',
            'specialization' => 'required|string',
            'identity_type' => 'required|string',
            'identity_attachment' => 'required|file|mimes:jpeg,jpg,png,pdf',
            'admission_attachment' => 'required|file|mimes:pdf|max:20480',
        ];
        validateParam($request->all(), $rules);
        $user = auth("api")->user();

        $student = $user->Student;

        $studentBundle = BundleStudent::find($studentBundleId);

        if (!$student) {
            return apiResponse2(0, 'not_student', "you need to apply to diploma first");
        }

        if (!$studentBundle) {
            return apiResponse2(0, "doesn't match", "this is not your bundle url");
        }


        $studentRequirments = $studentBundle->studentRequirement;


        $identity_attachment = $request->file('identity_attachment');
        $identity_attachmentName =  $user->user_code . '_' . $request['identity_type'] . '.' . $identity_attachment->getClientOriginalExtension();
        $identity_attachmentPath = $identity_attachment->storeAs('studentRequirements', $identity_attachmentName);

        $admission_attachment = $request->file('admission_attachment');
        $admission_attachmentName =  $user->user_code . '_addmission.' . $admission_attachment->getClientOriginalExtension();
        $admission_attachmentPath = $admission_attachment->storeAs('studentRequirements', $admission_attachmentName);



        $data = [
            'bundle_student_id' => $studentBundle->id,
            'identity_type' => $request['identity_type'],
            'identity_attachment' => $identity_attachmentPath,
            'admission_attachment' => $admission_attachmentPath,
        ];

        if ($studentRequirments) {
            if ($studentRequirments->status != StudentRequirement::rejected) {
                return apiResponse2(1, 'already_upload', "You upload requirements before successfully, go to requirements section to view its status");
            }
            $data['status'] = StudentRequirement::pending;
            $studentRequirments->update($data);
        } else {
            StudentRequirement::create($data);
        }
        return apiResponse2(1, 'success', "requirements uploaded successfully, wait to be reviewed");
    }

    private function cleanUsersUtf8($collection)
    {
        return $collection->map(function ($item) {
            foreach ($item->getAttributes() as $key => $value) {
                if (is_string($value) && !mb_check_encoding($value, 'UTF-8')) {
                    $item->$key = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                }
            }
            return $item;
        });
    }


    public function students(Request $request, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $query = User::whereIn('role_name', [Role::$user, Role::$registered_user]);

        $totalStudents = clone $query;
        $inactiveStudents = clone $query;
        $banStudents = clone $query;
        $totalStudents = $totalStudents->count();
        $inactiveStudents = $inactiveStudents->where('status', 'inactive')->count();
        $banStudents = $banStudents
            ->where('ban', true)
            ->whereNotNull('ban_end_at')
            ->where('ban_end_at', '>', time())
            ->count();

        $userGroups = Group::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
        $query = $this->filters($query, $request);

        if ($is_export_excel) {
            $users = $query->orderBy('created_at', 'desc')->get();
        } else {
            $users = $query->orderBy('created_at', 'desc')->get();
        }

        $users = $query->with([
            'student.bundleStudent.bundle',
            'programTranslation'
        ])->orderBy('created_at', 'desc')->get();
        $users = $this->addUsersExtraInfo($users);

        $category = Category::where('parent_id', '!=', null)->get();
        $users = $users->map(function ($user) {
            $student = $user->student;
            if ($student) {
                $user->student_id = $student->id;
                $user->identity_img = $student->identity_img;
                $user->bundles = $student->bundleStudent ? $student->bundleStudent->map(function ($bundleStudent) {
                    return $bundleStudent->bundle;
                }) : collect();
            } else {
                $user->student_id = null;
                $user->identity_img = null;
                $user->bundles = collect();
            }
            $user->program = $user->programTranslation ?? null;
            return $user;
        });
        $users = $this->cleanUsersUtf8($users);
        return response()->json([
            'students' => $users,
            'category' => $category,
            'totalStudents' => $totalStudents,
            'inactiveStudents' => $inactiveStudents,
            'banStudents' => $banStudents,
            'userGroups' => $userGroups,
        ], 200);
    }

    public function addUsersExtraInfo($users)
    {
        foreach ($users as $user) {
            $salesQuery = Sale::where('seller_id', $user->id)
                ->whereNull('refund_at');

            $classesSaleQuery = deepClone($salesQuery)->whereNotNull('webinar_id')
                ->whereNull('meeting_id')
                ->whereNull('promotion_id')
                ->whereNull('subscribe_id');

            $user->classesSalesCount = $classesSaleQuery->count();
            $user->classesSalesSum = $classesSaleQuery->sum('total_amount');

            $meetingIds = Meeting::where('creator_id', $user->id)->pluck('id');
            $reserveMeetingsQuery = ReserveMeeting::whereIn('meeting_id', $meetingIds)
                ->where(function ($query) {
                    $query->whereHas('sale', function ($query) {
                        $query->whereNull('refund_at');
                    });

                    $query->orWhere(function ($query) {
                        $query->whereIn('status', ['canceled']);
                        $query->whereHas('sale');
                    });
                });

            $user->meetingsSalesCount = deepClone($reserveMeetingsQuery)->count();
            $user->meetingsSalesSum = deepClone($reserveMeetingsQuery)->sum('paid_amount');

            $purchasedQuery = Sale::where('buyer_id', $user->id)
                ->whereNull('refund_at');

            $classesPurchasedQuery = deepClone($purchasedQuery)->whereNotNull('webinar_id')
                ->whereNull('meeting_id')
                ->whereNull('promotion_id')
                ->whereNull('subscribe_id');

            $user->classesPurchasedsCount = $classesPurchasedQuery->count();
            $user->classesPurchasedsSum = $classesPurchasedQuery->sum('total_amount');

            $meetingsPurchasedQuery = deepClone($purchasedQuery)->whereNotNull('meeting_id')
                ->whereNull('webinar_id')
                ->whereNull('promotion_id')
                ->whereNull('subscribe_id');

            $user->meetingsPurchasedsCount = $meetingsPurchasedQuery->count();
            $user->meetingsPurchasedsSum = $meetingsPurchasedQuery->sum('total_amount');
        }

        return $users;
    }

    public function coursesList(Request $request)
    {
        $query = Webinar::where(['unattached' => 1, 'hasGroup' => 1])->withCount('groups');
        $webinars = $this->coursesListFilter($query, $request)->paginate(10);
        // return view('admin.students.coursesList', compact('webinars'));
        return response()->json([
            'webinars' => $webinars,
        ], 200);
    }

    public function coursesListFilter($query, $request)
    {
        $title = $request->input('title', null);
        $from = $request->input('from', null);
        $to = $request->input('to', null);

        $query = fromAndToDateFilter($from, $to, $query, 'start_date');
        if ($title) {
            $query->whereTranslationLike('title', '%' . $title . '%')
                ->orWhere('slug', 'like', '%' . $title . '%');
        }
        return $query;
    }

    public function RegisteredUsers(Request $request, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $query = User::where(['role_name' => Role::$registered_user])->whereDoesntHave('student')->with('programTranslation');

        $query = $this->filters($query, $request);

        if ($is_export_excel) {
            $users = $query->orderBy('created_at', 'desc')->get();
        } else {
            $users = $query->orderBy('created_at', 'desc')
                ->get();
        }

        $users = $this->addUsersExtraInfo($users);

        if ($is_export_excel) {
            return $users;
        }

        $data = [
            'users' => $users,
        ];

        return response()->json($data);
    }

    public function filters($query, $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $user_name = $request->get('user_name');
        $full_name = $request->get('full_name');
        $user_code = $request->get('user_code');
        $ar_name = $request->get('ar_name');
        $email = $request->get('email');
        $mobile = $request->get('mobile');
        $sort = $request->get('sort');
        $group_id = $request->get('group_id');
        $status = $request->get('status');
        $role_id = $request->get('role_id');
        $organization_id = $request->get('organization_id');
        $program = $request->get('program', null);

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($full_name)) {
            $query->where('full_name', 'like', "%$full_name%");
        }

        if (!empty($user_name)) {
            $query->where(function ($q) use ($user_name) {
                $q->where('full_name', 'like', "%$user_name%")
                    ->orWhereHas('student', function ($q) use ($user_name) {
                        $q->where('ar_name', 'like', "%$user_name%")
                            ->orWhere('en_name', 'like', "%$user_name%");
                    });
            });
        }

        if (!empty($user_code)) {
            $query->where('user_code', 'like', "%$user_code%");
        }
        if (!empty($ar_name)) {
            $query->whereHas('student', function ($q) use ($ar_name) {
                $q->where('ar_name', 'like', "%$ar_name%");
                $q->orWhere('en_name', 'like', "%$ar_name%");
            });
        }
        if (!empty($email)) {
            $query->where('email', 'like', "%$email%");
        }
        if (!empty($mobile)) {
            $query->where('mobile', 'like', "%$mobile%");
        }
        if (!empty($program)) {
            $query->where(function ($q1) use ($program) {
                $q1->whereHas('purchases', function ($query2) use ($program) {
                    $query2->whereHas(
                        'bundle',
                        function ($q) use ($program) {
                            $q->whereTranslationLike('title', '%' . $program . '%')
                                ->orWhere('slug', 'like', "%$program%");
                        }
                    )
                        ->orWhereHas(
                            'webinar',
                            function ($q) use ($program) {
                                $q->whereTranslationLike('title', '%' . $program . '%')
                                    ->orWhere('slug', 'like', "%$program%");
                            }
                        );
                })
                    ->orWhereHas('appliedProgram', function ($q) use ($program) {
                        $q->whereTranslationLike('title', '%' . $program . '%')
                            ->orWhere('slug', 'like', "%$program%");
                    });
            });
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'sales_classes_asc':
                    $query->join('sales', 'users.id', '=', 'sales.seller_id')
                        ->select('users.*', 'sales.seller_id', 'sales.webinar_id', 'sales.refund_at', DB::raw('count(sales.seller_id) as sales_count'))
                        ->whereNotNull('sales.webinar_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.seller_id')
                        ->orderBy('sales_count', 'asc');
                    break;
                case 'sales_classes_desc':
                    $query->join('sales', 'users.id', '=', 'sales.seller_id')
                        ->select('users.*', 'sales.seller_id', 'sales.webinar_id', 'sales.refund_at', DB::raw('count(sales.seller_id) as sales_count'))
                        ->whereNotNull('sales.webinar_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.seller_id')
                        ->orderBy('sales_count', 'desc');
                    break;
                case 'purchased_classes_asc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.refund_at', DB::raw('count(sales.buyer_id) as purchased_count'))
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.buyer_id')
                        ->orderBy('purchased_count', 'asc');
                    break;
                case 'purchased_classes_desc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.refund_at', DB::raw('count(sales.buyer_id) as purchased_count'))
                        ->groupBy('sales.buyer_id')
                        ->whereNull('sales.refund_at')
                        ->orderBy('purchased_count', 'desc');
                    break;
                case 'purchased_classes_amount_asc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.amount', 'sales.refund_at', DB::raw('sum(sales.amount) as purchased_amount'))
                        ->groupBy('sales.buyer_id')
                        ->whereNull('sales.refund_at')
                        ->orderBy('purchased_amount', 'asc');
                    break;
                case 'purchased_classes_amount_desc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.amount', 'sales.refund_at', DB::raw('sum(sales.amount) as purchased_amount'))
                        ->groupBy('sales.buyer_id')
                        ->whereNull('sales.refund_at')
                        ->orderBy('purchased_amount', 'desc');
                    break;
                case 'sales_appointments_asc':
                    $query->join('sales', 'users.id', '=', 'sales.seller_id')
                        ->select('users.*', 'sales.seller_id', 'sales.meeting_id', 'sales.refund_at', DB::raw('count(sales.seller_id) as sales_count'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.seller_id')
                        ->orderBy('sales_count', 'asc');
                    break;
                case 'sales_appointments_desc':
                    $query->join('sales', 'users.id', '=', 'sales.seller_id')
                        ->select('users.*', 'sales.seller_id', 'sales.meeting_id', 'sales.refund_at', DB::raw('count(sales.seller_id) as sales_count'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.seller_id')
                        ->orderBy('sales_count', 'desc');
                    break;
                case 'purchased_appointments_asc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.meeting_id', 'sales.refund_at', DB::raw('count(sales.buyer_id) as purchased_count'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.buyer_id')
                        ->orderBy('purchased_count', 'asc');
                    break;
                case 'purchased_appointments_desc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.meeting_id', 'sales.refund_at', DB::raw('count(sales.buyer_id) as purchased_count'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.buyer_id')
                        ->orderBy('purchased_count', 'desc');
                    break;
                case 'purchased_appointments_amount_asc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.amount', 'sales.meeting_id', 'sales.refund_at', DB::raw('sum(sales.amount) as purchased_amount'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.buyer_id')
                        ->orderBy('purchased_amount', 'asc');
                    break;
                case 'purchased_appointments_amount_desc':
                    $query->join('sales', 'users.id', '=', 'sales.buyer_id')
                        ->select('users.*', 'sales.buyer_id', 'sales.amount', 'sales.meeting_id', 'sales.refund_at', DB::raw('sum(sales.amount) as purchased_amount'))
                        ->whereNotNull('sales.meeting_id')
                        ->whereNull('sales.refund_at')
                        ->groupBy('sales.buyer_id')
                        ->orderBy('purchased_amount', 'desc');
                    break;
                case 'register_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'register_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        }

        if (!empty($group_id)) {
            // $userIds = GroupUser::where('group_id', $group_id)->pluck('user_id')->toArray();
            $userIds = Enrollment::where('group_id', $group_id)->pluck('user_id')->toArray();

            $query->whereIn('id', $userIds);
        }

        if (!empty($status)) {
            switch ($status) {
                case 'active_verified':
                    $query->where('status', 'active')
                        ->where('verified', true);
                    break;
                case 'active_notVerified':
                    $query->where('status', 'active')
                        ->where('verified', false);
                    break;
                case 'inactive':
                    $query->where('status', 'inactive');
                    break;
                case 'ban':
                    $query->where('ban', true)
                        ->whereNotNull('ban_end_at')
                        ->where('ban_end_at', '>', time());
                    break;
            }
        }

        if (!empty($role_id)) {
            $query->where('role_id', $role_id);
        }

        if (!empty($organization_id)) {
            $query->where('organ_id', $organization_id);
        }

        //dd($query->get());
        return $query;
    }

    public function Users(Request $request, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $query = User::whereHas('student')->whereHas('purchasedFormBundleUnique');

        $salaQuery = Sale::whereNull('refund_at')
            ->whereHas('buyer')
            ->where('type', 'form_fee')
            ->whereNotNull('bundle_id')
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('sales as s2')
                    ->whereRaw('s2.bundle_id = sales.bundle_id')
                    ->where(function ($query) {
                        $query->where('s2.type', 'bundle')
                            ->orWhere('s2.type', 'installment_payment')
                            ->orWhere('s2.type', 'bridging');
                    })
                    ->whereRaw('s2.buyer_id = sales.buyer_id');
            })
            ->where("payment_method", "!=", 'scholarship')
            ->with(['buyer', 'bundle'])
            ->orderBy('buyer_id', 'desc')
            ->groupBy(['buyer_id', 'bundle_id']);

        $query = (new SaleController())->getSalesFilters($salaQuery, $request);

        $sales = $query->orderBy('created_at', 'desc')->get();

        return response()->json($sales, 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function Enrollers(Request $request, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $salaQuery = Sale::whereNull('refund_at')
            ->whereNotNull(['bundle_id', 'buyer_id'])
            ->whereHas('buyer')
            ->whereIn('type', ['bundle', 'installment_payment', 'bridging'])
            ->where("payment_method", "!=", 'scholarship')
            ->with(['buyer', 'bundle'])
            ->orderBy('buyer_id', 'desc')
            ->groupBy(['buyer_id', 'bundle_id']);

        $query = (new SaleController())->getSalesFilters($salaQuery, $request);

        if ($is_export_excel) {
            $sales = $query->orderBy('created_at', 'desc')->get();
        } else {
            $sales = $query->orderBy('created_at', 'desc')->get();
        }
        return response()->json($sales, 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function directRegister(Request $request, $is_export_excel = false)
    {
        $this->authorize('admin_users_list');

        $query = BundleStudent::whereHas('student')->whereNull('class_id')->with(['student.user', 'bundle']);

        if ($is_export_excel) {
            $bundlstudents = $query->orderBy('student_id', 'desc')->get();
        } else {
            $bundlstudents = $query->orderBy('student_id', 'desc')->orderBy('created_at', 'desc')
                ->get();
        }
        return response()->json($bundlstudents, 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function getPurchasedClassesData($user)
    {
        $manualAddedClasses = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('webinar_id')
            ->where('sales.manual_added', true)
            ->where('sales.access_to_purchased_item', true)
            ->whereHas('webinar')
            ->with([
                'webinar',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $manualDisabledClasses = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('webinar_id')
            ->where('sales.access_to_purchased_item', false)
            ->whereHas('webinar')
            ->with([
                'webinar',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $purchasedClasses = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('webinar_id')
            ->where('sales.access_to_purchased_item', true)
            ->where('sales.manual_added', false)
            ->whereHas('webinar')
            ->with([
                'webinar',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'manualAddedClasses' => $manualAddedClasses,
            'purchasedClasses' => $purchasedClasses,
            'manualDisabledClasses' => $manualDisabledClasses,
        ];
    }

    public function getPurchasedBundlesData($user)
    {
        $manualAddedBundles = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('bundle_id')
            ->where('sales.manual_added', true)
            ->where('sales.access_to_purchased_item', true)
            ->whereHas('bundle')
            ->with([
                'bundle',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $manualDisabledBundles = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('bundle_id')
            ->where('sales.access_to_purchased_item', false)
            ->whereHas('bundle')
            ->with([
                'bundle',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $purchasedBundles = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('bundle_id')
            ->where('sales.access_to_purchased_item', true)
            ->where('sales.manual_added', false)
            ->whereHas('bundle')
            ->with([
                'bundle',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'manualAddedBundles' => $manualAddedBundles,
            'purchasedBundles' => $purchasedBundles,
            'manualDisabledBundles' => $manualDisabledBundles,
        ];
    }

    public function getPurchasedProductsData($user)
    {
        $manualAddedProducts = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('product_order_id')
            ->where('sales.manual_added', true)
            ->where('sales.access_to_purchased_item', true)
            ->whereHas('productOrder')
            ->with([
                'productOrder' => function ($query) {
                    $query->with([
                        'product',
                    ]);
                },
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $manualDisabledProducts = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('product_order_id')
            ->where('sales.access_to_purchased_item', false)
            ->whereHas('productOrder')
            ->with([
                'productOrder' => function ($query) {
                    $query->with([
                        'product',
                    ]);
                },
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $purchasedProducts = Sale::whereNull('refund_at')
            ->where('buyer_id', $user->id)
            ->whereNotNull('product_order_id')
            ->where('sales.access_to_purchased_item', true)
            ->where('sales.manual_added', false)
            ->whereHas('productOrder')
            ->with([
                'productOrder' => function ($query) {
                    $query->with([
                        'product',
                    ]);
                },
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'manualAddedProducts' => $manualAddedProducts,
            'purchasedProducts' => $purchasedProducts,
            'manualDisabledProducts' => $manualDisabledProducts,
        ];
    }

    public function edit(Request $request, $url_name, $id)
    {
        Log::info('Request full_name: ' . $request->input('full_name'));
        $this->authorize('admin_users_edit');
        $organization = Organization::where('url_name', $url_name)->first();
        if (!$organization) {
            return response()->json(['message' => 'Cannot found this organization'], 404);
        }

        $user = User::where('id', $id)
            ->with([
                'customBadges' => function ($query) {
                    $query->with('badge');
                },
                'occupations' => function ($query) {
                    $query->with('category');
                },
                'userRegistrationPackage',
            ])
            ->first();

        if (!$user) {
            abort(404);
        }


        if (!empty($user->location)) {
            $user->location = \Geo::getST_AsTextFromBinary($user->location);

            $user->location = \Geo::get_geo_array($user->location);
        }

        $userMetas = $user->userMetas;

        if (!empty($userMetas)) {
            foreach ($userMetas as $meta) {
                $user->{$meta->name} = $meta->value;
            }
        }

        $becomeInstructor = null;
        if (!empty($request->get('type')) and $request->get('type') == 'check_instructor_request') {
            $becomeInstructor = BecomeInstructor::where('user_id', $user->id)
                ->first();
        }

        $categories = Category::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $occupations = $user->occupations->pluck('category_id')->toArray();

        $userBadges = $user->getBadges(false);

        $roles = Role::all();
        $badges = Badge::all();

        $userLanguages = getGeneralSettings('user_languages');
        if (!empty($userLanguages) and is_array($userLanguages)) {
            $userLanguages = getLanguages($userLanguages);
        } else {
            $userLanguages = [];
        }

        $provinces = null;
        $cities = null;
        $districts = null;

        $countries = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
            ->where('type', Region::$country)
            ->get();

        if (!empty($user->country_id)) {
            $provinces = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                ->where('type', Region::$province)
                ->where('country_id', $user->country_id)
                ->get();
        }

        if (!empty($user->province_id)) {
            $cities = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                ->where('type', Region::$city)
                ->where('province_id', $user->province_id)
                ->get();
        }

        if (!empty($user->city_id)) {
            $districts = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
                ->where('type', Region::$district)
                ->where('city_id', $user->city_id)
                ->get();
        }

        $userBanks = UserBank::query()
            ->with([
                'specifications',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [
            'user' => $user,
            'userBadges' => $userBadges,
            'roles' => $roles,
            'badges' => $badges,
            'categories' => $categories,
            'occupations' => $occupations,
            'becomeInstructor' => $becomeInstructor,
            'userLanguages' => $userLanguages,
            'userRegistrationPackage' => $user->userRegistrationPackage,
            'countries' => $countries,
            'provinces' => $provinces,
            'cities' => $cities,
            'districts' => $districts,
            'userBanks' => $userBanks,
        ];

        // Purchased Classes Data
        $data = array_merge($data, $this->getPurchasedClassesData($user));

        // Purchased Bundles Data
        $data = array_merge($data, $this->getPurchasedBundlesData($user));

        // Purchased Product Data
        $data = array_merge($data, $this->getPurchasedProductsData($user));

        if (auth()->user()->can('admin_forum_topics_lists')) {
            $data['topics'] = ForumTopic::where('creator_id', $user->id)
                ->with([
                    'posts' => function ($query) {
                        $query->orderBy('created_at', 'desc');
                    },
                    'forum',
                ])
                ->withCount('posts')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        return $data;
    }
}
