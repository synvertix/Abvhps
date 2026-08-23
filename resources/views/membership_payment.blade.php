@extends('layouts.app')

@section('content')
<section class="min-h-[500px] flex items-center justify-center bg-gray-50 py-12 px-4">
    <div class="max-w-md w-full bg-white p-8 rounded-xl shadow border border-gray-100 text-center">
        <div class="w-16 h-16 rounded-full overflow-hidden bg-white border-2 border-brandOrange shadow-sm mx-auto mb-2 flex items-center justify-center p-0.5 shrink-0">
            <img src="{{ asset('images/ABVHPS_LOGO.jpg') }}" class="w-full h-full object-cover rounded-full" alt="ABVHPS Logo">
        </div>
        <h2 class="mt-2 text-2xl font-extrabold text-brandGray">Membership Fee Payment</h2>
        <p class="text-xs text-gray-500 mt-1">Akhanda Bharatha Viswa Hindu Parirakshana Samiti</p>
        
        <div class="my-8 p-6 bg-brandLightOrange rounded-lg border border-orange-100">
            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-1">Total Amount Payable</span>
            <span class="text-4xl font-black text-brandOrange">₹100.00</span>
            <div class="h-[1px] bg-orange-200 my-3"></div>
            <p class="text-xs text-brandGray font-medium">Verified Phone: <strong class="tracking-wider">+91 {{ session('verified_membership_phone') }}</strong></p>
        </div>

        <div id="payment-error-alert" class="hidden mb-4 p-3 bg-red-50 text-red-700 text-xs rounded-md border border-red-200 text-left">
            <span id="payment-error-message"></span>
        </div>

        <div class="space-y-4">
            <p class="text-xs text-gray-500 leading-relaxed">
                Complete your ₹100 membership fee payment securely via Razorpay Checkout. Upon successful verification, your 12-digit membership registration process will proceed to Aadhaar verification.
            </p>

            <button type="button" id="pay-button" onclick="startMembershipPayment()"
                class="w-full flex justify-center items-center py-3 px-4 border border-transparent text-sm font-bold rounded-md text-white bg-brandOrange hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brandOrange shadow transition disabled:opacity-50">
                <span id="button-text">Pay ₹100 Securely Now</span>
                <span id="button-spinner" class="hidden ml-2">
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </div>

        <div class="mt-4">
            <a href="/membership" class="text-xs font-bold text-brandGray hover:text-brandOrange uppercase tracking-wide">&larr; Change Phone Number</a>
        </div>
    </div>
</section>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    function showError(msg) {
        const alertBox = document.getElementById('payment-error-alert');
        const alertMsg = document.getElementById('payment-error-message');
        if (alertBox && alertMsg) {
            alertMsg.textContent = msg;
            alertBox.classList.remove('hidden');
        }
    }

    function hideError() {
        const alertBox = document.getElementById('payment-error-alert');
        if (alertBox) {
            alertBox.classList.add('hidden');
        }
    }

    function setButtonLoading(isLoading) {
        const btn = document.getElementById('pay-button');
        const text = document.getElementById('button-text');
        const spinner = document.getElementById('button-spinner');
        if (btn) btn.disabled = isLoading;
        if (spinner) {
            if (isLoading) spinner.classList.remove('hidden');
            else spinner.classList.add('hidden');
        }
    }

    function startMembershipPayment() {
        hideError();
        setButtonLoading(true);

        const csrfToken = '{{ csrf_token() }}';

        fetch('{{ route("membership.payment.razorpay.initiate") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.already_paid && data.redirect_url) {
                window.location.href = data.redirect_url;
                return;
            }

            if (!data.success) {
                showError(data.message || 'Unable to initialize Razorpay payment order.');
                setButtonLoading(false);
                return;
            }

            const options = {
                key: data.key_id,
                amount: data.amount_paise,
                currency: data.currency || 'INR',
                name: 'ABVHPS',
                description: 'Membership Fee Payment',
                order_id: data.order_id,
                prefill: {
                    contact: '{{ session("verified_membership_phone") }}'
                },
                theme: {
                    color: '#f97316'
                },
                handler: function (response) {
                    setButtonLoading(true);
                    fetch('{{ route("membership.payment.razorpay.verify") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_signature: response.razorpay_signature
                        })
                    })
                    .then(res => res.json())
                    .then(verifyData => {
                        if (verifyData.success && verifyData.redirect_url) {
                            window.location.href = verifyData.redirect_url;
                        } else {
                            showError(verifyData.message || 'Payment verification failed server-side.');
                            setButtonLoading(false);
                        }
                    })
                    .catch(err => {
                        showError('Network error during payment verification. Please try again.');
                        setButtonLoading(false);
                    });
                },
                modal: {
                    ondismiss: function() {
                        setButtonLoading(false);
                    }
                }
            };

            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function (response) {
                showError(response.error.description || 'Payment process failed or was declined.');
                setButtonLoading(false);
            });
            rzp.open();
        })
        .catch(err => {
            showError('Communication error initiating payment order.');
            setButtonLoading(false);
        });
    }
</script>
@endsection
