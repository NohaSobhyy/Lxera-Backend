<?php

namespace App\Http\Controllers\Admin;

use App\Exports\OfflinePaymentsExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\PaymentController;
use App\Models\Accounting;
use App\Models\OfflineBank;
use App\Models\OfflinePayment;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Role;
use App\User;
use App\BundleStudent;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class OfflinePaymentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_offline_payments_list');

        $pageType = $request->get('page_type', 'requests'); //requests or history

        $query = OfflinePayment::query();
        // if ($pageType == 'requests') {
        //     $query->where('status', OfflinePayment::$waiting);
        // } else {
        //     $query->where('status', '!=', OfflinePayment::$waiting);
        // }

        $query = $this->filters($query, $request);

        $offlinePayments = $query->paginate(5);

        $offlinePayments->appends([
            'page_type' => $pageType
        ]);

        $roles = Role::all();

        $offlineBanks = OfflineBank::query()
            ->orderBy('created_at', 'desc')
            ->with([
                'specifications'
            ])
            ->get();

        $data = [
            'pageTitle' => trans('admin/main.offline_payments_title') . (($pageType == 'requests') ? 'Requests' : 'History'),
            'offlinePayments' => $offlinePayments,
            'pageType' => $pageType,
            'roles' => $roles,
            'offlineBanks' => $offlineBanks,
        ];

        $user_ids = $request->get('user_ids', []);

        if (!empty($user_ids)) {
            $data['users'] = User::select('id', 'full_name')
                ->whereIn('id', $user_ids)->get();
        }

        return view('admin.financial.offline_payments.lists', $data);
    }

    private function filters($query, $request)
    {
        $from = $request->get('from', null);
        $to = $request->get('to', null);
        $search = $request->get('search', null);
        $user_ids = $request->get('user_ids', []);
        $role_id = $request->get('role_id', null);
        $account_type = $request->get('account_type', null);
        $sort = $request->get('sort', null);
        $status = $request->get('status', null);

        if (!empty($search)) {
            $ids = User::where('full_name', 'like', "%$search%")->pluck('id')->toArray();
            $user_ids = array_merge($user_ids, $ids);
        }

        if (!empty($role_id)) {
            $role = Role::where('id', $role_id)->first();

            if (!empty($role)) {
                $ids = $role->users()->pluck('id')->toArray();
                $user_ids = array_merge($user_ids, $ids);
            }
        }

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($user_ids) and count($user_ids)) {
            $query->whereIn('user_id', $user_ids);
        }

        if (!empty($account_type)) {
            $query->where('offline_bank_id', $account_type);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'amount_asc':
                    $query->orderBy('amount', 'asc');
                    break;
                case 'amount_desc':
                    $query->orderBy('amount', 'desc');
                    break;
                case 'pay_date_asc':
                    $query->orderBy('pay_date', 'asc');
                    break;
                case 'pay_date_desc':
                    $query->orderBy('pay_date', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    public function reject(Request $request, OfflinePayment $offlinePayment)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required'
        ]);

        $this->authorize('admin_offline_payments_reject');

        $bundleTitle = $offlinePayment->order->orderItems->first()->bundle->title;
        if ($offlinePayment->pay_for == 'form_fee') {
            $purpuse = 'لحجز مقعد دراسي ';
            $status = 'fee_rejected';
        } elseif ($offlinePayment->pay_for == 'bundle') {
            $purpuse = 'للدفع الكامل ل  ' . $bundleTitle;
            $status = 'rejected';
        } elseif ($offlinePayment->pay_for == 'installment') {
            $purpuse = 'لدفع ' . ($offlinePayment->order->orderItems->first()->installmentPayment->step->installmentStep->title ?? 'القسط الأول') . ' من ' . $bundleTitle;
            if (!empty($offlinePayment->order->orderItems->first()->installmentPayment->step->installmentStep)) {
                $status = 'approved';
            } else {
                $status = 'rejected';
            }
        } elseif ($offlinePayment->pay_for == 'service') {
            $purpuse = 'لدفع رسوم خدمة  ' . ($offlinePayment->order->orderItems->first()->service->title);
            $offlinePayment->order->orderItems
                ->first()
                ->service->users()
                ->where('user_id', $offlinePayment->user_id)
                ->first()->pivot->update(['status' => 'paying_rejected']);
        } else {
            $purpuse = '';
            $status = 'rejected';
        }

        $data['body'] = "لقد تم رفض طلبك  " . $purpuse . ' بسبب ' . $request['reason'];

        $message =  $request['reason'] . "<br>";
        if (isset($request['message'])) {
            $data['body'] =  $data['body'] . "\n" . $request['message'];
            $message .= $request['message'];
        }
        $offlinePayment->update(['status' => OfflinePayment::$reject, 'message' => $message]);
        BundleStudent::where(['student_id' => $offlinePayment->user->student->id, 'bundle_id' => $offlinePayment->order->orderItems->first()->bundle_id])->update(['status' => $status]);

        $notifyOptions = [
            '[amount]' => handlePrice($offlinePayment->amount),
            '[p.body]' =>  $data['body']
        ];


        sendNotification('offline_payment_rejected', $notifyOptions, $offlinePayment->user_id);

        return back();
    }

    public function approved(Request $request, $id)
    {
        $this->authorize('admin_offline_payments_approved');

        $offlinePayment = OfflinePayment::findOrFail($id);

        if ($offlinePayment->order_id) {

            $offlinePayment->order->update(['status' => Order::$paying]);
            $PaymentController = new PaymentController();
            $PaymentController->paymentOrderAfterVerify($offlinePayment->order);
            $request->merge(['order_id' => $offlinePayment->order_id]);
            $res = $PaymentController->payStatus($request);

            BundleStudent::where(['student_id' => $offlinePayment->user->student->id, 'bundle_id' => $offlinePayment->order->orderItems->first()->bundle_id])->update(['status' => 'approved']);

            $bundleTitle = $offlinePayment->order->orderItems->first()->bundle->title ?? '';
            if ($offlinePayment->pay_for == 'form_fee') {
                $purpuse = 'لحجز مقعد دراسي ';
            } elseif ($offlinePayment->pay_for == 'bundle') {
                $purpuse = 'للدفع الكامل ل  ' . $bundleTitle;
            } elseif ($offlinePayment->pay_for == 'installment') {
                $purpuse = 'لدفع ' . ($offlinePayment->order->orderItems->first()->installmentPayment->step->installmentStep->title ?? 'القسط الأول') . ' من ' . $bundleTitle;
            } elseif ($offlinePayment->pay_for == 'webinar') {
                $purpuse = 'لدفع دورة ' . ($offlinePayment->order->orderItems->first()->webinar->title);
            } elseif ($offlinePayment->pay_for == 'service') {
                $purpuse = 'لدفع رسوم خدمة  ' . ($offlinePayment->order->orderItems->first()->service->title);
                
                $offlinePayment->order->orderItems
                    ->first()
                    ->service->users()
                    ->where('user_id', $offlinePayment->user_id)
                    ->first()->pivot->update(['status' => 'pending']);
            } else {
                $purpuse = '';
            }

            $notifyOptions = [
                '[amount]' => handlePrice($offlinePayment->amount),
                '[p.body]' => "لقد تم قبول طلبك  " . $purpuse
            ];
            sendNotification('offline_payment_approved', $notifyOptions, $offlinePayment->user_id);
        } else {
            Accounting::create([
                'creator_id' => auth()->user()->id,
                'user_id' => $offlinePayment->user_id,
                'amount' => $offlinePayment->amount,
                'type' => Accounting::$addiction,
                'type_account' => Accounting::$asset,
                'description' => trans('admin/pages/setting.notification_offline_payment_approved'),
                'created_at' => time(),
            ]);

            $accountChargeReward = RewardAccounting::calculateScore(Reward::ACCOUNT_CHARGE, $offlinePayment->amount);
            RewardAccounting::makeRewardAccounting($offlinePayment->user_id, $accountChargeReward, Reward::ACCOUNT_CHARGE);

            $chargeWalletReward = RewardAccounting::calculateScore(Reward::CHARGE_WALLET, $offlinePayment->amount);
            RewardAccounting::makeRewardAccounting($offlinePayment->user_id, $chargeWalletReward, Reward::CHARGE_WALLET);
        }

        $offlinePayment->update(['status' => OfflinePayment::$approved]);
        return back();
    }

    public function exportExcel(Request $request)
    {
        $pageType = $request->get('page_type', 'requests'); //requests or history

        $query = OfflinePayment::query();
        if ($pageType == 'requests') {
            $query->where('status', OfflinePayment::$waiting);
        } else {
            $query->where('status', '!=', OfflinePayment::$waiting);
        }

        $query = $this->filters($query, $request);

        $offlinePayments = $query->get();

        $export = new OfflinePaymentsExport($offlinePayments);

        return Excel::download($export, 'offline_payment_' . $pageType . '.xlsx');
    }
}
