@extends(getTemplate() . '.layouts.app')


@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
    <style>
        .charge-amount {

            font-family: "IBM Plex Sans Arabic" !important;

            font-style: normal;
            font-weight: 600;
            line-height: normal;
        }
    </style>
@endpush



@section('content')


    @php
        $title = "<h1 class='font-30 text-white font-weight-bold'>" . trans('cart.checkout') . '</h1>';
        $subTitle = "<span class='payment-hint font-20 text-white d-block'>";

        $hero = "
    <h1 class='mb-15 font-36'>تهانينا !</h1>
    <h3 class='mb-2'>أنت الآن أقرب من أي وقت لتحقيق أهدافك ..</h3>
    <h3>لم يتبق سوى خطوة واحدة للانطلاق في رحلتك التعليمية .</h3>
    ";
        $mainTitleText = ' البرنامج الدراسي :';
        $mainTitleValue = '';
        $subTitleText = 'رسوم البرنامج :';
        $detailsTitle = 'تفاصيل البرنامج';

        $subTitleValue = handlePrice($order->total_amount);

        if ($count > 0) {
            $subTitle .= $total . ' ريال سعودي ' . trans('cart.for_items', ['count' => $count]);
        } elseif (!empty($order->orderItems[0]->service)) {
            $detailsTitle = 'تفاصيل الخدمة';
            $mainTitleText = ' عنوان الخدمة:';
            $mainTitleValue = $order->orderItems[0]->service->title;
            $subTitleText = 'رسوم الخدمة :';

            $subTitle .= 'الرسوم لطلب خدمة ' . $order->orderItems[0]->service->title;
        } elseif (!empty($type) && $type == 1) {
            $subTitle .= 'رسوم حجز مقعد ';

            $mainTitleText = ' البرنامج الدراسي :';
            $mainTitleValue = $order->orderItems[0]->bundle->title;
            $subTitleText = 'رسوم حجز مقعد :';
        } elseif (!empty($order->orderItems[0]->bundle)) {
            $mainTitleText = ' البرنامج الدراسي :';
            $mainTitleValue = $order->orderItems[0]->bundle->title;
            $subTitleText = 'رسوم البرنامج :';

            $subTitle .= 'الرسوم الدراسية للبرنامج ' . $order->orderItems[0]->bundle->title;
        } elseif (!empty($order->orderItems[0]->webinar)) {
            $detailsTitle = 'تفاصيل الدورة';
            $mainTitleText = ' الدورة الدراسية :';
            $mainTitleValue = $order->orderItems[0]->webinar->title;
            $subTitleText = 'رسوم الدورة الدراسية :';

            $subTitle .= 'الرسوم الدراسية للدورة ' . $order->orderItems[0]->webinar->title;
        }
        // close subtitle
        $subTitle .=
            ': <span class="price"> ' .
            handlePrice($total) .
            '</span> <span class="price-with-discount"></span>
</span>';
    @endphp

    @include('web.default.includes.hero_section', ['inner' => $hero])

    <section class="container mt-45">

        @if (!empty($totalCashbackAmount))
            <div class="d-flex align-items-center mb-25 p-15 success-transparent-alert">
                <div class="success-transparent-alert__icon d-flex align-items-center justify-content-center">
                    <i data-feather="credit-card" width="18" height="18" class=""></i>
                </div>

                <div class="ml-10">
                    <div class="font-14 font-weight-bold ">{{ trans('update.get_cashback') }}</div>
                    <div class="font-12 ">
                        {{ trans('update.by_purchasing_this_cart_you_will_get_amount_as_cashback', ['amount' => handlePrice($totalCashbackAmount)]) }}
                    </div>
                </div>
            </div>
        @endif

        @php

            $showOfflineFields = false;
            if ($errors->any() or !empty($editOfflinePayment)) {
                $showOfflineFields = true;
            }

            $isMultiCurrency = !empty(getFinancialCurrencySettings('multi_currency'));
            $userCurrency = currency();
            $invalidChannels = [];
        @endphp

        <div class="row">
            <section class="col-12 row mb-40">
                <h3 class="section-title mb-20 text-secondary font-weight-bold">{{ $detailsTitle }}</h3>
                <div class="col-12 rounded-sm shadow p-20 px-40">

                    <div class="cart-checkout-item border-0 d-flex row justify-content-start">
                        <h4 class="font-14 font-weight-bold ">{{ $mainTitleText }}</h4>
                        <span class="font-14 font-weight-500 ml-20">{{ $mainTitleValue }}</span>
                    </div>

                    <div class="cart-checkout-item border-0 d-flex row justify-content-start">
                        <h4 class="font-14 font-weight-bold ">{{ $subTitleText }}</h4>
                        <span class="font-14 font-weight-500 ml-30">{{ $subTitleValue }}</span>
                    </div>
                </div>
            </section>
        </div>

        <h2 class="section-title text-secondary">{{ trans('financial.select_a_payment_gateway') }}</h2>

        <form action="/payments/payment-request" method="post" class=" mt-25" enctype="multipart/form-data" id="cartForm">
            {{ csrf_field() }}
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            <input type="hidden" name="discount_id" id="discount_id" value="{{ $order->orderItems[0]->discount_id }}">



            <div class="row">
                {{-- online  --}}
                @if (!empty($paymentChannels))
                    @foreach ($paymentChannels as $paymentChannel)
                        @if (!$isMultiCurrency or !empty($paymentChannel->currencies) and in_array($userCurrency, $paymentChannel->currencies))
                            <div class="col-12 mb-40 charge-account-radio ">
                                <input type="radio" name="gateway" class="online-gateway" checked
                                    id="{{ $paymentChannel->title }}" data-class="{{ $paymentChannel->class_name }}"
                                    value="{{ $paymentChannel->id }}">
                                <label class="d-flex row justify-content-start align-items-center p-20 px-20 rounded-sm"
                                    for="{{ $paymentChannel->title }}" class="col-12 rounded-sm shadow p-20 px-40">
                                    {{-- <img src="{{ $paymentChannel->image }}" width="120" height="60" alt=""> --}}
                                    <div>
                                        @include('web.default.cart.includes.online_payment_icon')
                                    </div>

                                    <p class=" font-weight-bold text-dark-blue ml-20">
                                        {{ trans('financial.pay_via') }}
                                        <span class="font-weight-bold font-14">{{ $paymentChannel->title }}</span>
                                    </p>
                                </label>
                            </div>
                        @else
                            @php
                                $invalidChannels[] = $paymentChannel;
                            @endphp
                        @endif
                    @endforeach
                @endif

                {{-- offline --}}
                @if (!empty(getOfflineBankSettings('offline_banks_status')))
                    <div class="col-12 mb-40 charge-account-radio ">
                        <input type="radio" name="gateway" id="offline" value="offline"
                            @if (old('gateway') == 'offline' or !empty($editOfflinePayment)) checked @endif>
                        <label class="d-flex row justify-content-start align-items-center p-20 px-20 rounded-sm"
                            for="offline" class="col-12 rounded-sm shadow p-20 px-40">

                            <div>
                                @include('web.default.cart.includes.offline_payment_icon')
                            </div>

                            <p class=" font-weight-bold text-dark-blue ml-20">
                                {{ trans('financial.pay_via') }}
                                <span class="font-weight-bold font-14">{{ trans('financial.offline') }}</span>
                            </p>
                        </label>
                    </div>
                @endif

                @error('gateway')
                    <div class="invalid-feedback d-block"> {{ $message }}</div>
                @enderror

                {{-- account discharge --}}
                {{--
                <div class="col-6 col-lg-4 mb-40 charge-account-radio">
                    <input type="radio" @if (empty($userCharge) or $total > $userCharge) disabled @endif name="gateway" id="offline"
                        value="credit">
                    <label for="offline"
                        class="rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center">
                        <img src="/assets/default/img/activity/pay.svg" width="120" height="60" alt="">

                        <p class="mt-30 mt-lg-50 font-weight-500 text-dark-blue">
                            {{ trans('financial.account') }}
                        <span class="font-weight-bold">{{ trans('financial.charge') }}</span>
                        </p>
                        <span class="mt-5">{{ handlePrice($userCharge) }}</span>
                    </label>
                </div>
                --}}
            </div>

            @if (!empty($invalidChannels))
                <div class="d-flex align-items-center mt-30 rounded-lg border p-15">
                    <div class="size-40 d-flex-center rounded-circle bg-gray200">
                        <i data-feather="info" class="text-gray" width="20" height="20"></i>
                    </div>
                    <div class="ml-5">
                        <h4 class="font-14 font-weight-bold text-gray">{{ trans('update.disabled_payment_gateways') }}</h4>
                        <p class="font-12 text-gray">{{ trans('update.disabled_payment_gateways_hint') }}</p>
                    </div>
                </div>

                <div class="row mt-20">
                    @foreach ($invalidChannels as $invalidChannel)
                        <div class="col-6 col-lg-4 mb-40 charge-account-radio">
                            <div
                                class="disabled-payment-channel bg-white border rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center">
                                <img src="{{ $invalidChannel->image }}" width="120" height="60" alt="">

                                <p class="mt-30 mt-lg-50 font-weight-500 text-dark-blue">
                                    {{ trans('financial.pay_via') }}
                                    <span class="font-weight-bold font-14">{{ $invalidChannel->title }}</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- offline banks --}}
            @if (!empty(getOfflineBankSettings('offline_banks_status')))
                <section class="mt-40 js-offline-payment-input mb-3"
                    style="{{ !$showOfflineFields ? 'display:none' : '' }}">
                    <h2 class="section-title">{{ trans('financial.bank_accounts_information') }}</h2>

                    <div class="row mt-25">
                        @foreach ($offlineBanks as $offlineBank)
                            <div class="col-12 col-lg-7 mb-30 mb-lg-0">
                                <div
                                    class="py-25 px-20 rounded-sm panel-shadow d-flex flex-column align-items-center justify-content-center">
                                    <img src="{{ $offlineBank->logo }}" width="120" height="60" alt="">

                                    <div class="mt-15 mt-30 w-100">

                                        <div class="d-flex align-items-center justify-content-between">
                                            <span
                                                class="font-14 font-weight-500 text-secondary">{{ trans('public.name') }}:</span>
                                            <span
                                                class="font-14 font-weight-500 text-gray">{{ $offlineBank->title }}</span>
                                        </div>

                                        @foreach ($offlineBank->specifications as $specification)
                                            <div class="d-flex align-items-center justify-content-between mt-10">
                                                <span
                                                    class="font-14 font-weight-500 text-secondary">{{ $specification->name }}:</span>
                                                <span
                                                    class="font-14 font-weight-500 text-gray">{{ $specification->value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- offline inputs --}}
                <div class="">
                    <h3 class="section-title mb-20 js-offline-payment-input"
                        style="{{ !$showOfflineFields ? 'display:none' : '' }}">{{ trans('financial.finalize_payment') }}
                    </h3>

                    <div class="row">

                        <div class="col-12 col-lg-3 mb-25 mb-lg-0 js-offline-payment-input "
                            style="{{ !$showOfflineFields ? 'display:none' : '' }}">
                            <div class="form-group">
                                <label class="input-label">{{ trans('financial.account') }}</label>
                                <select name="account" class="form-control @error('account') is-invalid @enderror">
                                    <option selected disabled>{{ trans('financial.select_the_account') }}</option>

                                    @foreach ($offlineBanks as $offlineBank)
                                        <option value="{{ $offlineBank->id }}"
                                            @if (old('account') == $offlineBank->id) selected @endif>{{ $offlineBank->title }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('account')
                                    <div class="invalid-feedback"> {{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                        <div class="col-12 col-lg-3 mb-25 mb-lg-0 js-offline-payment-input "
                            style="{{ !$showOfflineFields ? 'display:none' : '' }}">
                            <div class="form-group">
                                <label for="IBAN" class="input-label"> اي بان (IBAN)</label>
                                <input type="text" name="IBAN" id="IBAN" value="{{ old('IBAN') }}"
                                    class="form-control @error('IBAN') is-invalid @enderror" />
                                @error('IBAN')
                                    <div class="invalid-feedback"> {{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12 col-lg-3 mb-25 mb-lg-0 js-offline-payment-input "
                            style="{{ !$showOfflineFields ? 'display:none' : '' }}">
                            <div class="form-group">
                                <label class="input-label">{{ trans('update.attach_the_payment_photo') }}</label>

                                <label for="attachmentFile" id="attachmentFileLabel"
                                    class="custom-upload-input-group flex-row-reverse ">
                                    <span class="custom-upload-icon text-white">
                                        <i data-feather="upload" width="18" height="18" class="text-white"></i>
                                    </span>
                                    <div class="custom-upload-input"></div>
                                </label>

                                <input type="file" name="attachment" id="attachmentFile" accept=".jpeg,.jpg,.png"
                                    class="form-control h-auto invisible-file-input @error('attachment') is-invalid @enderror"
                                    value="" />
                                @error('attachment')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>


                    </div>
                </div>
            @endif



            {{-- discount section --}}
            <div class="row mt-30">

                {{-- discount --}}
                <section class="col-12 row">
                    <h3 class="section-title d-none">{{ trans('cart.coupon_code') }}</h3>

                    {{-- order section --}}
                    <h3 class="section-title mb-20 text-secondary">تفاصيل عملية الدفع</h3>

                    <div class="col-12 rounded-sm shadow p-20 pb-20 px-40 ">
                        <div class="cart-checkout-item border-0">
                            <h4 class="text-secondary font-14 font-weight-500">
                                {{ trans('cart.sub_total') }}</h4>
                            <span
                                class="font-14 text-gray font-weight-bold" id="mainTotal">{{ handlePrice($order->total_amount) }}</span>
                        </div>
                        @if (!empty($enableCoupon))
                            <div class="cart-checkout-item border-0">
                                <h4 class="text-secondary font-14 font-weight-500">
                                    {{ trans('public.discount') }}
                                    <span id="discount_percent">(0%)</span>
                                </h4>
                                <span class="font-14 text-gray font-weight-bold">
                                    <span id="totalDiscount">0 ر.س </span>
                                </span>
                            </div>
                        @endif
                        <div class="cart-checkout-item border-0 chargeSectionText">
                            <h4 class="text-gray300 font-14 font-weight-500 chargeSectionText">خصم من المحفظة</h4>
                            <span class="font-14 text-gray300 font-weight-bold chargeSectionText">
                                <span id="totalCharge">
                                    0 ر.س
                                </span>
                            </span>
                        </div>

                        <div class="col-12 rounded-sm  mt-10 " id='discountSection'>

                            @if (!empty($userGroup) and !empty($userGroup->discount))
                                <p class="text-gray mt-25">
                                    {{ trans('cart.in_user_group', ['group_name' => $userGroup->name, 'percent' => $userGroup->discount]) }}
                                </p>
                            @endif

                            {{-- discount section --}}
                            @if (!empty($enableCoupon))
                                <div class="d-flex row  justify-content-between" id="couponSection">
                                    <div class="form-group col-10 p-0 mb-0">
                                        <input type="hidden" id="order_input" value="{{ $order->id }}">
                                        <input type="text" name="coupon" id="coupon_input"
                                            class=" form-control   border-pink p-3"
                                            placeholder="{{ trans('cart.enter_your_code_here') }}">
                                        <span class="invalid-feedback">{{ trans('cart.coupon_invalid') }}</span>
                                        <span class="valid-feedback">{{ trans('cart.coupon_valid') }}</span>
                                    </div>

                                    <p id="checkCoupon" class="col-1 btn btn-sm btn-primary h-35px border-thick p-3">
                                        {{ trans('cart.validate') }}</p>
                                </div>
                            @endif

                            {{-- charge section --}}
                            <div class="row  justify-content-between d-none" id="chargeSection">
                                <div class="col-9 form-control text-center text-primary border-pink">
                                    <h3 class="self-align-center charge-amount font-16">
                                        رصيد المحفظة :
                                        <span id="userCharge">
                                            {{ handlePrice($userCharge) }}
                                        </span>

                                    </h3>
                                </div>
                                <button type="button" id="checkBalance"
                                    class="col-2 btn btn-sm btn-primary h-35px border-thick p-3">استخدام
                                    الرصيد</button>
                                <input type="checkbox" id="sub_from_amount" name="sub_from_amount" class="d-none">
                            </div>

                        </div>
                        {{-- total amount section --}}
                        <div class="cart-checkout-item border-top border-gray300 border-bottom-0 mt-40">
                            <h4 class="text-secondary font-14 font-weight-bold">{{ trans('cart.total') }}
                            </h4>
                            <span class="font-14 text-secondary font-weight-bold">
                                <span id="totalAmount">{{ handlePrice($order->total_amount) }}</span>
                            </span>
                        </div>
                    </div>

                </section>


            </div>

            {{-- payment details buttons --}}
            <div class="d-flex align-items-center justify-content-end mt-45 px-20">

                <div class="d-flex align-items-center justify-content-end mt-45 px-20">
                    <button type="button" id="backToPrevious"
                        class="btn btn-sm btn btn-outline-primary px-50  h-35px border-thick p-3 ml-3 d-none"> السابق
                    </button>

                    <button type="button" id="backToProgram"
                        class="btn btn-sm btn btn-outline-primary px-50  h-35px border-thick p-3 ml-3"
                        onclick="returnBack(event)"> السابق
                    </button>

                    <button type="button" id="nextBtn"
                        class="btn btn-sm btn-primary px-50  h-35px border-thick p-3">التالى
                    </button>

                    <button type="submit" id="paymentSubmit"
                        class="btn btn-sm btn-primary px-50  h-35px border-thick p-3 d-none">الدفع
                    </button>
                </div>

            </div>
        </form>


        @if (!empty($razorpay) and $razorpay)
            <form action="/payments/verify/Razorpay" method="get">
                <input type="hidden" name="order_id" value="{{ $order->id }}">

                <script src="https://checkout.razorpay.com/v1/checkout.js" data-key="{{ env('RAZORPAY_API_KEY') }}"
                    data-amount="{{ (int) ($order->total_amount * 100) }}" data-buttontext="product_price" data-description="Rozerpay"
                    data-currency="{{ currency() }}" data-image="{{ $generalSettings['logo'] }}"
                    data-prefill.name="{{ $order->user->full_name }}" data-prefill.email="{{ $order->user->email }}"
                    data-theme.color="#43d477"></script>
            </form>
        @endif

    </section>

@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/parts/payment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

    <script src="/assets/default/js/panel/financial/account.min.js"></script>



    <script src="/assets/default/js//parts/main.min.js"></script>
    <script src="/assets/default/js/panel/public.min.js"></script>
    <script>
        var couponInvalidLng = "{{ trans('cart.coupon_invalid') }}";
        var selectProvinceLang = "{{ trans('update.select_province') }}";
        var selectCityLang = "{{ trans('update.select_city') }}";
        var selectDistrictLang = "{{ trans('update.select_district') }}";
    </script>
    <script src="/assets/default/js/parts/cart.min.js"></script>

    <script>
        window.onload = function() {
            // let discountCheckbox = document.getElementById('discount-checkbox');
            // let discountSection = document.getElementById('discountSection');

            // let chargeCheckboxWrapper = document.getElementById('charge-2');
            // let chargeCheckbox = document.getElementById('charge-checkbox');


            const nextBtn = document.getElementById('nextBtn');
            const finalPaymentSunmitButton = document.getElementById('paymentSubmit');
            const couponSection = document.getElementById('couponSection');
            const chargeSection = document.getElementById('chargeSection');
            const checkBalance = document.getElementById('checkBalance');
            const backToPrevious = document.getElementById('backToPrevious');
            const backToProgram = document.getElementById('backToProgram');

            const totalAmount = document.getElementById('totalAmount');
            const totalCharge = document.getElementById('totalCharge');

            const userCharge = @json($userCharge);
            const enableCoupon = @json($enableCoupon);

            const userChargeSpan = document.getElementById('userCharge');

            const checkInput = document.getElementById('sub_from_amount');

            nextBtn.onclick = function(e) {
                chargeSection.classList.remove('d-none');
                chargeSection.classList.add('d-flex');
                if (enableCoupon) {
                    couponSection.classList.remove('d-flex');
                    couponSection.classList.add('d-none');
                }
                $(".chargeSectionText").addClass('text-primary');
                finalPaymentSunmitButton.classList.remove('d-none');
                backToPrevious.classList.remove('d-none');
                backToProgram.classList.add('d-none');
                nextBtn.classList.add('d-none');

            }

            checkBalance.onclick = function(e) {

                checkInput.setAttribute('checked', true);

                totalCharge.textContent = (parseInt(userCharge) < parseInt(totalAmount.textContent) ? parseInt(
                        userCharge) :
                    parseInt(totalAmount.textContent)) + ' ر.س ';

                userChargeSpan.textContent = (parseInt(userCharge) > (parseInt(totalAmount.textContent)) ? (
                    parseInt(
                        userCharge) - parseInt(
                        totalAmount.textContent)) : 0) + ' ر.س ';
                // totalAmount.classList.add('d-none');
                totalAmount.textContent = ((parseInt(totalAmount.textContent) > parseInt(userCharge)) ? (parseInt(
                    totalAmount.textContent) - parseInt(userCharge)) : 0) + ' ر.س ';
                checkBalance.setAttribute('disabled', true);
            }

            backToPrevious.onclick = function(e) {
                checkBalance.removeAttribute('disabled');
                totalAmount.textContent = parseInt(totalAmount.textContent) + parseInt(totalCharge.textContent) +
                    ' ر.س';
                chargeSection.classList.remove('d-flex');
                chargeSection.classList.add('d-none');
                finalPaymentSunmitButton.classList.add('d-none');
                backToPrevious.classList.add('d-none');
                backToProgram.classList.remove('d-none');
                nextBtn.classList.remove('d-none');
                if (enableCoupon) {
                    couponSection.classList.add('d-flex');
                    couponSection.classList.remove('d-none');
                }
                totalCharge.textContent = '0 ر.س';
                $(".chargeSectionText").removeClass('text-primary');
                checkInput.removeAttribute('checked');

                userChargeSpan.textContent = userCharge + ' ر.س';
            }

        }


        function returnBack(e){
            let previousUrl = document.referrer;
            console.log(previousUrl);
            window.location.href=previousUrl;
        }
    </script>
@endpush
