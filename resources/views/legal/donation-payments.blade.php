@extends('legal.layout', [
    'docTitle' => 'Donation & Payment Policy',
    'docDescription' => 'How donations and online payments work at ' . config('abvhps.legal.short') . ' — use of funds, receipts and tax exemption (80G / 12A), Razorpay and Cashfree payments, payment security and fraud safety.',
])

@section('summary')
    <ul class="!my-0 space-y-1.5 text-[14px]">
        <li>Donations are voluntary gifts used for the campaign you choose; any surplus goes to the Trust&rsquo;s similar charitable objects.</li>
        <li>Payments are processed by <strong>Razorpay</strong> and <strong>Cashfree</strong> on their secure pages. We never see or store your card, UPI PIN or bank password.</li>
        <li>For an 80G receipt we need your name and PAN. Tax benefit depends on the law and your own situation.</li>
        <li>ABVHPS will <strong>never ask for your OTP, PIN or password</strong> by phone, WhatsApp or e-mail.</li>
    </ul>
@endsection

@section('legal')
<section class="legal-section" id="donations">
    <h2>1. Donations</h2>
    <ul>
        <li>A donation is a <strong>voluntary gift</strong> made out of your own free will, from your own lawful funds, for charitable purposes. It creates no membership, ownership, interest or right to goods or services.</li>
        <li>You may donate to a specific campaign or to the Trust&rsquo;s general objects. The campaign page shows its purpose and, where available, its target and the amount raised.</li>
        <li>Donations are accepted in <strong>Indian Rupees</strong> through the payment options shown at checkout. The minimum and any suggested amounts are shown on the donation page.</li>
        <li>Refunds are covered by the <a href="{{ route('legal.refund') }}">Refund &amp; Cancellation Policy</a>: in general a donation is not refundable.</li>
    </ul>
</section>

<section class="legal-section" id="use-of-funds" data-toc="Use of funds">
    <h2>2. How donations are used</h2>
    <ul>
        <li>Funds are used for the campaign or purpose you chose, in keeping with the objects of the Trust and the law.</li>
        <li>If a campaign is fully funded, cannot be completed, or the need has changed, the Trust may apply the balance to the same cause or to a similar charitable object of the Trust, so that your gift is never wasted.</li>
        <li>A reasonable part of contributions may be used for the genuine costs of running a campaign and the Trust, within the limits the law allows for charitable trusts.</li>
        <li>We publish campaign progress on the website. The Trust keeps accounts and is subject to audit and to the tax laws that apply to charitable trusts.</li>
    </ul>
</section>

<section class="legal-section" id="tax" data-toc="Receipts &amp; tax exemption">
    <h2>3. Receipts and tax exemption (80G / 12A)</h2>
    <ul>
        <li>After a successful payment you can download an official <strong>donation receipt</strong> with a receipt number, and we send it to your e-mail. Keep it for your records.</li>
        <li>The Trust&rsquo;s registration and tax-exemption certificates (such as 12A and 80G) are published on the <a href="{{ url('/compliance-certificates') }}">Compliance Certificates</a> page, with the validity stated on them.</li>
        <li>To claim a deduction under section 80G your name and <strong>PAN</strong> must be given correctly at the time of donation. If you enter a wrong name or PAN, tell us promptly so that we can correct the record.</li>
        <li>Whether a donation qualifies, and how much can be claimed, depends on the certificate then in force, the law, and your own tax position. Please take advice from your tax adviser. ABVHPS does not guarantee any tax benefit.</li>
        <li>Membership fees, volunteer fees and exam fees are <strong>not donations</strong> and are not eligible for an 80G receipt unless the receipt itself says so.</li>
        <li>If we are required to report donations to the tax authorities, we will do so using the details you gave.</li>
    </ul>
</section>

<section class="legal-section" id="gateways" data-toc="Payment gateways">
    <h2>4. Payment gateways: Razorpay and Cashfree</h2>
    <p>Online payments (donations, the membership fee and exam fees) are collected through <strong>Razorpay</strong> and <strong>Cashfree Payments</strong>, which are licensed payment aggregators / gateways in India. Which one you see depends on the payment and is shown at checkout.</p>
    <ul>
        <li><strong>Payment methods</strong> are those offered by the gateway on its secure page, such as UPI, debit / credit cards, net banking and wallets. Availability depends on the gateway and your bank.</li>
        <li><strong>You pay on the gateway&rsquo;s page or secure pop-up.</strong> Your card, UPI PIN, CVV, OTP and bank login are entered only there. ABVHPS does not receive, see or store them.</li>
        <li>The gateways follow the security standards required of them (including PCI-DSS for card data). Their own terms and privacy policies apply to the payment: see <a href="https://razorpay.com/terms/" rel="noopener noreferrer" target="_blank">Razorpay</a> and <a href="https://www.cashfree.com/terms-and-conditions/" rel="noopener noreferrer" target="_blank">Cashfree</a>.</li>
        <li><strong>What we keep:</strong> the order and payment IDs, amount, status, time and the payer details you gave us, in order to confirm the payment, issue receipts and keep accounts.</li>
        <li><strong>Confirmation.</strong> A payment counts as successful only when the gateway confirms it to us. We check this confirmation for authenticity (signature verification) before we issue a receipt, membership ID or exam confirmation, so a forged or altered payment cannot succeed.</li>
        <li><strong>Delays.</strong> Banks and gateways can be slow. If your status is pending, wait for it to update before paying again. See section 5 of the Refund &amp; Cancellation Policy.</li>
        <li><strong>Charges.</strong> The amount you are asked to pay is shown before you confirm. Any gateway or bank charge added to it is shown at checkout; we do not add hidden charges.</li>
        <li><strong>Payments are in INR</strong> and must be made from an instrument that belongs to you or that you are authorised to use.</li>
    </ul>
</section>

<section class="legal-section" id="foreign" data-toc="Foreign contributions">
    <h2>5. Contributions from outside India</h2>
    <p>Our online payment options are meant for contributions in Indian Rupees from Indian payment instruments. If you live outside India and would like to help, please contact us before contributing. Any contribution from a foreign source is accepted only where the law, including the Foreign Contribution (Regulation) Act, 2010, allows it.</p>
</section>

<section class="legal-section" id="safety" data-toc="Staying safe from fraud">
    <h2>6. Staying safe from fraud</h2>
    <ul>
        <li>Pay only on <a href="{{ $legal['website'] }}">{{ parse_url($legal['website'], PHP_URL_HOST) }}</a> or in the official ABVHPS app, and check the address in your browser before you pay.</li>
        <li>ABVHPS will <strong>never</strong> ask you for your OTP, PIN, CVV or password, and will never ask you to send money to a personal account or UPI ID to &ldquo;confirm&rdquo; a donation or membership.</li>
        <li>Nobody is authorised to collect cash or online payment in ABVHPS&rsquo;s name unless they show a valid ABVHPS ID and a receipt from this website. You can verify an ID from its QR code.</li>
        <li>If you suspect a fraud or an unauthorised transaction, tell your bank immediately and write to us with the details.</li>
    </ul>
</section>

<section class="legal-section" id="compliance" data-toc="Verification &amp; compliance">
    <h2>7. Verification and legal compliance</h2>
    <p>To follow anti-money-laundering, tax and other laws, and to protect the Trust, we may ask for identity or PAN details, delay or decline a payment that looks suspicious, and share information with authorities where the law requires. We may refuse or return a contribution that appears to break the law or that is inconsistent with the Trust&rsquo;s objects.</p>
</section>

<section class="legal-section" id="privacy-link" data-toc="Your data">
    <h2>8. Your data</h2>
    <p>The details you give when paying are handled as described in our <a href="{{ route('legal.privacy') }}">Privacy Policy</a>. We do not publish your name with a donation unless you ask us to.</p>
</section>

<section class="legal-section" id="changes" data-toc="Changes">
    <h2>9. Changes to this policy</h2>
    <p>We may update this policy, for example when payment options or the law change. The version and dates at the top show the latest one, and changes apply to payments made after they take effect.</p>
</section>
@endsection
