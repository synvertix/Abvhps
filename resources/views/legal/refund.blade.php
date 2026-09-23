@extends('legal.layout', [
    'docTitle' => 'Refund & Cancellation Policy',
    'docDescription' => 'When and how money paid to ' . config('abvhps.legal.short') . ' — donations, membership fees, exam fees — can be refunded or cancelled, and what happens if a payment fails or is charged twice.',
])

@section('summary')
    <ul class="!my-0 space-y-1.5 text-[14px]">
        <li><strong>Donations are voluntary gifts and are not refundable</strong>, except for a duplicate, mistaken or technically failed payment.</li>
        <li>Membership and exam fees are not refundable once the service is delivered, but we refund a fee if we could not deliver it or it was charged twice.</li>
        <li>If money was deducted but the payment shows as failed, the bank / gateway normally returns it automatically within about 5&ndash;7 working days.</li>
        <li>Tell us within {{ $legal['refund_request_days'] }} days of the payment, with your transaction ID, and we will respond within {{ $legal['acknowledge_days'] }} working days.</li>
    </ul>
@endsection

@section('legal')
<section class="legal-section" id="scope">
    <h2>1. What this policy covers</h2>
    <p>This policy applies to money paid to {{ $legal['short'] }} through the website or mobile app: <strong>donations</strong> (including campaign contributions), the <strong>membership fee</strong>, <strong>exam application fees</strong> and any other fee we may charge. It should be read with the <a href="{{ route('legal.donation_payments') }}">Donation &amp; Payment Policy</a> and the <a href="{{ route('legal.terms') }}">Terms &amp; Conditions</a>. Payments are made in Indian Rupees through Razorpay or Cashfree.</p>
</section>

<section class="legal-section" id="table" data-toc="At a glance">
    <h2>2. At a glance</h2>
    <div class="table-wrap">
    <table>
        <thead><tr><th style="width:22%">Payment</th><th style="width:34%">Refund allowed?</th><th>Cancellation</th></tr></thead>
        <tbody>
            <tr>
                <td><strong>Donation</strong></td>
                <td>No, it is a voluntary gift. Exceptions: duplicate payment, wrong amount entered by mistake (reported promptly), or a payment that was debited but not recorded.</td>
                <td>Cannot be cancelled after the payment succeeds. An unfinished payment simply lapses.</td>
            </tr>
            <tr>
                <td><strong>Membership fee</strong></td>
                <td>No once the membership is completed and the membership ID is issued. Yes if the fee was paid but we could not complete your membership, if it was charged twice, or if you were wrongly charged.</td>
                <td>You may stop before paying. After payment, the membership is treated as taken.</td>
            </tr>
            <tr>
                <td><strong>Exam application fee</strong></td>
                <td>No once the application is accepted, whether or not you appear. Yes if the exam is cancelled by ABVHPS, if the fee was charged twice, or if you were rejected as ineligible for a reason on our side.</td>
                <td>Applications can be withdrawn, but the fee is not returned unless one of the exceptions applies. If the exam is postponed, your application carries over.</td>
            </tr>
            <tr>
                <td><strong>Failed / pending payment</strong></td>
                <td>Money debited for a failed payment is returned by your bank or the gateway, normally within 5&ndash;7 working days.</td>
                <td>Do not pay again until the status is clear (see section 4).</td>
            </tr>
        </tbody>
    </table>
    </div>
</section>

<section class="legal-section" id="donations" data-toc="Donations">
    <h2>3. Donations</h2>
    <p>A donation is a voluntary gift for charitable purposes; you receive nothing in return, and we begin to use it for the cause soon after we receive it. For that reason we do not refund donations because you have changed your mind. We will refund a donation only where:</p>
    <ul>
        <li>the same donation was paid more than once (we refund the extra payment);</li>
        <li>you entered a wrong amount by mistake and tell us within {{ $legal['refund_request_days'] }} days, before the money has been used; or</li>
        <li>the money reached us but the donation could not be recorded or receipted.</li>
    </ul>
    <p>If a donation is refunded, the receipt issued for it is cancelled and cannot be used for a tax claim.</p>
    <p>If a campaign is over-funded, or cannot be carried out, contributions are used for the same or similar charitable objects of the Trust, as explained in the Donation &amp; Payment Policy.</p>
</section>

<section class="legal-section" id="fees" data-toc="Membership &amp; exam fees">
    <h2>4. Membership and exam fees</h2>
    <p>These fees pay for verification, administration, the ID card or hall ticket and the running of the programme. Once the service has been delivered (the membership ID is issued, or the exam application is accepted and processed), the fee is not refundable. We refund the full fee where:</p>
    <ul>
        <li>the fee was charged twice or by mistake;</li>
        <li>the payment succeeded but, because of a problem on our side, your membership or application could not be completed;</li>
        <li>an exam is cancelled by ABVHPS and no alternative date is offered, or you prefer a refund instead of the new date.</li>
    </ul>
    <p>We do not refund where an application is rejected or cancelled because false information or documents were given, or because you did not meet published eligibility rules that were stated before you paid.</p>
</section>

<section class="legal-section" id="failed" data-toc="Failed payments">
    <h2>5. Money deducted but payment failed or pending</h2>
    <ol class="legal-list">
        <li>Do not pay again straight away. Note your <strong>transaction / order ID</strong> from the payment screen, your bank message or the gateway receipt.</li>
        <li>Open the payment status page or wait a few minutes: confirmation from the bank sometimes arrives late and the status then changes to successful.</li>
        <li>If the payment stays failed, the amount is automatically reversed to your account by the bank or gateway, normally within <strong>5&ndash;7 working days</strong> (timelines are set by your bank and the gateway, not by us).</li>
        <li>If it has not been reversed, or shows successful but you did not get a receipt, membership ID or exam confirmation, write to us with the transaction ID and we will check with the gateway.</li>
    </ol>
</section>

<section class="legal-section" id="how" data-toc="How to request a refund">
    <h2>6. How to ask for a refund</h2>
    <ul>
        <li>E-mail <a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a> or call / WhatsApp {{ $contact['phone'] }}, giving your name, phone, e-mail, the amount, date, <strong>transaction ID</strong>, what the payment was for and why you are asking.</li>
        <li>Send the request within <strong>{{ $legal['refund_request_days'] }} days</strong> of the payment. Late requests are considered only for duplicate or unrecorded payments.</li>
        <li>We acknowledge within {{ $legal['acknowledge_days'] }} working days and may ask for proof of payment or identity.</li>
    </ul>
</section>

<section class="legal-section" id="processing" data-toc="Refund processing">
    <h2>7. How approved refunds are paid</h2>
    <ul>
        <li>Approved refunds are made <strong>only to the original payment method</strong> (the same card, UPI ID, bank account or wallet), never in cash or to another person&rsquo;s account.</li>
        <li>We initiate the refund within {{ $legal['refund_process_days'] }} working days of approval. After that, the bank or gateway usually takes a few more working days to show it in your account.</li>
        <li>The refund is of the amount you paid. If the gateway or bank has charged a fee that it does not return, we will tell you before refunding.</li>
    </ul>
</section>

<section class="legal-section" id="chargebacks" data-toc="Chargebacks &amp; disputes">
    <h2>8. Chargebacks and disputes</h2>
    <p>Please contact us before raising a chargeback with your bank; most problems can be solved faster directly. If a chargeback is raised for a payment that was properly made and delivered, we will provide the gateway and bank with the records they ask for, and may cancel the related membership, application or receipt.</p>
</section>

<section class="legal-section" id="changes" data-toc="Changes">
    <h2>9. Changes to this policy</h2>
    <p>We may update this policy. The version and dates at the top show the latest one. A change applies to payments made after it takes effect.</p>
</section>
@endsection
