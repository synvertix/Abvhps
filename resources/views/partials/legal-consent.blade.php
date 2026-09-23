{{--
    Short, plain-language notice placed next to every submit / pay button, linking to the full policies.
    Usage: @include('partials.legal-consent', ['type' => 'membership'])
    Types: membership | volunteer | wing | exam | donation | payment | contact
--}}
@php
    $type = $type ?? 'contact';
    $notices = [
        'membership' => 'Your identity and personal details are used only to verify and administer your membership. They are shared only with the payment, identity-verification, SMS / e-mail and hosting partners that help us do this, and you can ask us to correct or delete them at any time. The membership fee is not refundable once your membership ID is issued.',
        'volunteer'  => 'Your identity, bank and nominee details and documents are used only to administer your volunteer service. Volunteering is voluntary and unpaid, and is not employment. You can ask us to correct or delete your data at any time, subject to records we must keep by law.',
        'wing'       => 'The details and documents you submit are used only to review your application and run this wing. Taking part is voluntary and unpaid, and is not employment. You can ask us to correct or delete your data at any time, subject to records we must keep by law.',
        'exam'       => 'If the applicant is under 18, the parent / guardian confirms consent to this application and to the use of the details given. A winner’s name, rank and photograph may be featured on the winners wall. The application fee is not refundable once the application is accepted, except where the exam is cancelled or the fee is charged twice.',
        'donation'   => 'A donation is a voluntary gift and is generally not refundable (duplicate or mistaken payments excepted). Payment is made on the secure Razorpay or Cashfree page: we never see or store your card, UPI PIN or bank password. Your PAN is used only to issue the 80G receipt.',
        'payment'    => 'Payment is made on the secure Razorpay page: we never see or store your card, UPI PIN or bank password. The membership fee is not refundable once your membership ID is issued; a duplicate or failed charge is returned as explained in the Refund & Cancellation Policy.',
        'contact'    => 'We use your details only to reply to your message and keep a record of the enquiry.',
    ];
    $text = $notices[$type] ?? $notices['contact'];
@endphp
<div class="legal-consent-notice mt-2 mb-3 rounded-lg border border-amber-200/80 bg-amber-50/60 px-3.5 py-3 text-left text-[11px] leading-relaxed text-gray-600" data-legal-notice="{{ $type }}">
    <p>
        <span aria-hidden="true">🔒</span>
        {{ $text }}
        By continuing you agree to our
        <a href="{{ route('legal.terms') }}" target="_blank" rel="noopener" class="font-bold text-brandOrange underline">Terms &amp; Conditions</a>,
        <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener" class="font-bold text-brandOrange underline">Privacy Policy</a>
        @if(in_array($type, ['membership', 'exam', 'donation', 'payment'], true))
            and <a href="{{ route('legal.refund') }}" target="_blank" rel="noopener" class="font-bold text-brandOrange underline">Refund &amp; Cancellation Policy</a>
        @endif
        .
    </p>
</div>
