@extends('legal.layout', [
    'docTitle' => 'Account & Data Deletion',
    'docDescription' => 'Ask ABVHPS to delete your account and personal data, or to show, correct or stop using it. This is also the data-deletion page for the ABVHPS mobile app.',
    'hideMeta' => false,
])

@section('summary')
    <ul class="!my-0 space-y-1.5 text-[14px]">
        <li>Use the form below (or e-mail us) to delete your account and data, get a copy, correct it or withdraw consent.</li>
        <li>We confirm it is really you, acknowledge within {{ $legal['acknowledge_days'] }} working days and complete within {{ $legal['resolve_days'] }} days.</li>
        <li>Some records, such as donation accounts, must be kept by law for a set period &mdash; we explain what is kept and why.</li>
    </ul>
@endsection

@section('legal')
<section class="legal-section" id="request" data-toc="Make a request">
    <h2>1. Make a request</h2>

    @if(session('request_sent'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800" role="status">
            Thank you. We have received your request. We will write to the e-mail you gave to confirm your identity and will acknowledge within {{ $legal['acknowledge_days'] }} working days.
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700" role="alert">
            <ul class="!my-0 list-disc pl-4">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('legal.data_request') }}" class="mt-4 space-y-4 not-legal-prose" id="data-request-form">
        @csrf
        <div style="position:absolute;left:-9999px" aria-hidden="true">
            <label>Leave this empty<input type="text" name="website_trap_honeypot" tabindex="-1" autocomplete="off"></label>
        </div>

        <div>
            <label for="request_type" class="block text-[11px] font-black uppercase tracking-wider text-gray-500 mb-1.5">What would you like us to do? *</label>
            <select id="request_type" name="request_type" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm font-semibold text-gray-800 focus:border-brandOrange focus:outline-none">
                @foreach($requestTypes as $value => $label)
                    <option value="{{ $value }}" @selected(old('request_type', 'deletion') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="dr_name" class="block text-[11px] font-black uppercase tracking-wider text-gray-500 mb-1.5">Full name *</label>
                <input id="dr_name" type="text" name="name" required maxlength="100" value="{{ old('name') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm font-semibold focus:border-brandOrange focus:outline-none">
            </div>
            <div>
                <label for="dr_email" class="block text-[11px] font-black uppercase tracking-wider text-gray-500 mb-1.5">E-mail *</label>
                <input id="dr_email" type="email" name="email" required maxlength="150" value="{{ old('email') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm font-semibold focus:border-brandOrange focus:outline-none">
            </div>
            <div>
                <label for="dr_phone" class="block text-[11px] font-black uppercase tracking-wider text-gray-500 mb-1.5">Registered mobile number</label>
                <input id="dr_phone" type="tel" name="phone" maxlength="20" value="{{ old('phone') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm font-semibold focus:border-brandOrange focus:outline-none">
            </div>
            <div>
                <label for="dr_member" class="block text-[11px] font-black uppercase tracking-wider text-gray-500 mb-1.5">Membership / volunteer ID (if any)</label>
                <input id="dr_member" type="text" name="membership_id" maxlength="40" value="{{ old('membership_id') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm font-semibold focus:border-brandOrange focus:outline-none">
            </div>
        </div>

        <div>
            <label for="dr_details" class="block text-[11px] font-black uppercase tracking-wider text-gray-500 mb-1.5">Details (optional)</label>
            <textarea id="dr_details" name="details" rows="4" maxlength="2000" placeholder="Tell us what to delete or correct. Please do not type passwords, OTPs, Aadhaar or bank numbers here." class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm font-semibold focus:border-brandOrange focus:outline-none">{{ old('details') }}</textarea>
        </div>

        <label class="flex items-start gap-2.5 text-[13px] font-semibold text-gray-700 cursor-pointer">
            <input type="checkbox" name="confirm" value="1" required class="mt-1 rounded border-gray-300 text-brandOrange focus:ring-brandOrange" @checked(old('confirm'))>
            <span>I confirm that these details are mine (or that I am the parent / guardian or nominee of the person concerned) and that I understand the points in section 3 below.</span>
        </label>

        <button type="submit" class="rounded-xl bg-brandOrange px-6 py-3 text-xs font-black uppercase tracking-wider text-white shadow-md hover:bg-orange-600 transition">Submit request</button>
        <p class="text-[12px] text-gray-500">Prefer e-mail? Write to <a href="mailto:{{ $officer['email'] }}">{{ $officer['email'] }}</a> from the address registered with us, quoting your name and phone number.</p>
    </form>
</section>

<section class="legal-section" id="process" data-toc="What happens next">
    <h2>2. What happens next</h2>
    <ol class="legal-list">
        <li>We reply to the e-mail you gave to confirm your identity (we may ask you to reply from your registered e-mail or to confirm a detail only you would know).</li>
        <li>We acknowledge your request within {{ $legal['acknowledge_days'] }} working days.</li>
        <li>We carry out the request and confirm to you, within {{ $legal['resolve_days'] }} days. If it will take longer we tell you why.</li>
    </ol>
</section>

<section class="legal-section" id="what-deleted" data-toc="What is deleted and what is kept">
    <h2>3. What is deleted and what we must keep</h2>
    <h3>Deleted or anonymised</h3>
    <ul>
        <li>Your membership, volunteer or wing profile and login, photograph and uploaded documents (identity, bank, health and family declarations, proofs).</li>
        <li>Your contact details and preferences, and your data in exam applications that are no longer needed for the result record.</li>
        <li>Your name, photograph and details on public pages such as Our Team, where you ask for their removal.</li>
    </ul>
    <h3>Kept where the law requires it</h3>
    <ul>
        <li><strong>Donation, fee and receipt records</strong> (name, PAN where given, amount, date, receipt number) for at least 8 years for accounting and income-tax purposes.</li>
        <li>Records needed to prevent fraud, to settle disputes or to meet a legal duty or court order, kept only for as long as needed.</li>
        <li>A short record of your request and our response, kept for 3 years as proof that we handled it.</li>
    </ul>
    <p>Deleting your data ends your membership or enrolment and its benefits, including your ID card. Deleted data cannot be restored, and you would need to apply again to rejoin.</p>
</section>

<section class="legal-section" id="app" data-toc="Deleting from the mobile app">
    <h2>4. Deleting your account from the mobile app</h2>
    <p>Open the ABVHPS app, choose <strong>Delete my account / data</strong> from the menu, and it will bring you to this page. Fill in the form above using the details registered with us. Uninstalling the app removes the sign-in token and language setting from your phone but does not delete your account or records held on our servers &mdash; use this form for that.</p>
</section>

<section class="legal-section" id="other-rights" data-toc="Your other rights">
    <h2>5. Your other rights</h2>
    <p>You can also ask us to show you what we hold, correct or complete it, stop a use you had agreed to, or handle a complaint. Choose the matching option in the form. For everything about how we handle data, read the <a href="{{ route('legal.privacy') }}">Privacy Policy</a>.</p>
</section>
@endsection
