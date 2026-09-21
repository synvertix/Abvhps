@extends('legal.layout', [
    'docTitle' => 'Privacy Policy',
    'docDescription' => 'How ' . config('abvhps.legal.short') . ' collects, uses, shares and protects your personal data on the website, the mobile app and our forms — and the rights you have under Indian law.',
])

@section('summary')
    <ul class="!my-0 space-y-1.5 text-[14px]">
        <li>We collect only what each activity needs &mdash; membership, volunteering, wings (Rudrasena, Kala Brundam, Grama Seva Dal, Organic Farmers), exams, donations and enquiries.</li>
        <li>We <strong>do not sell your data</strong> and we do not run advertising or third-party analytics trackers.</li>
        <li>Payments are processed by Razorpay and Cashfree. We never see or store your card, UPI PIN or net-banking password.</li>
        <li>Identity checks (including Aadhaar via DigiLocker / Cashfree Secure ID) are used only to verify you and prevent fake or duplicate memberships.</li>
        <li>You can ask us to show, correct or delete your data, or withdraw consent, at any time &mdash; see <a href="{{ route('legal.account_deletion') }}">Account &amp; Data Deletion</a>.</li>
    </ul>
@endsection

@section('legal')
<section class="legal-section" id="about">
    <h2>1. About this policy</h2>
    <p>This Privacy Policy explains how <strong>{{ $legal['entity'] }}</strong> ({{ $legal['registration'] }}), a registered charitable trust, referred to as <strong>&ldquo;ABVHPS&rdquo;, &ldquo;we&rdquo;, &ldquo;us&rdquo;</strong>, handles personal data of people who use <a href="{{ $legal['website'] }}">{{ parse_url($legal['website'], PHP_URL_HOST) }}</a>, the ABVHPS mobile app and the forms and services offered through them (together, the &ldquo;Services&rdquo;).</p>
    <p>For the purposes of the Digital Personal Data Protection Act, 2023 (&ldquo;DPDP Act&rdquo;) and the Information Technology Act, 2000 with its rules, ABVHPS is the <strong>Data Fiduciary</strong> and you are the <strong>Data Principal</strong>. By using the Services or submitting a form you confirm that you have read this policy. Where the law requires your specific consent, we ask for it separately (for example, the tick-boxes on our application forms).</p>
</section>

<section class="legal-section" id="data-we-collect" data-toc="Data we collect">
    <h2>2. Personal data we collect</h2>
    <p>What we collect depends on what you do. We ask only for information that the activity genuinely needs.</p>
    <div class="table-wrap">
    <table>
        <thead><tr><th style="width:24%">Activity</th><th>Data you give us</th></tr></thead>
        <tbody>
            <tr><td><strong>Contact &amp; enquiries</strong></td><td>Name, e-mail, phone (optional), subject and message. We also record the IP address and browser details of the submission to prevent spam and abuse.</td></tr>
            <tr><td><strong>Membership</strong></td><td>Mobile number (verified by OTP); full name, gender, date of birth, father&rsquo;s / husband&rsquo;s name, photograph, gotram, occupation, blood group, e-mail, permanent and present address, PIN code and local area (panchayat, mandal, assembly segment, district, state); identity verification details &mdash; Aadhaar number, or PAN / Voter ID / Driving Licence / Passport number &mdash; and the result of the verification; membership fee payment reference.</td></tr>
            <tr><td><strong>Volunteers</strong></td><td>Membership ID, mobile, e-mail, qualification, Voter ID number, bank details (bank, account holder, account number, IFSC, branch), nominee name / relation / phone, uploaded documents (self-declaration, Voter ID copy, bank proof), your location / cadre assignment and a password (stored only in scrambled, hashed form).</td></tr>
            <tr><td><strong>Rudrasena</strong></td><td>Membership ID, name, e-mail, mobile, date of birth and age, blood group, gotram, nominee details, bank details, and uploaded health-fitness declaration, family-consent declaration, ID proof and bank proof.</td></tr>
            <tr><td><strong>Kala Brundam &amp; Grama Seva Dal</strong></td><td>Team or village details, leader and member names, ages, mobile numbers, membership IDs and member photographs, and your acceptance of the disclaimer / charter.</td></tr>
            <tr><td><strong>Organic Farmers</strong></td><td>Membership ID, farmer name and mobile, land size, water source, number of indigenous cows, farming practices, crops and varieties, and your acceptance of the organic oath.</td></tr>
            <tr><td><strong>Exams</strong></td><td>Applicant (usually a student) name, date of birth, address, mobile, e-mail (verified by OTP), Aadhaar number, parent / guardian name, membership ID and contact, school or college and class, photograph, ID / signature image, Aadhaar proof, fee payment reference, and later hall-ticket number, marks, grade, rank and prizes.</td></tr>
            <tr><td><strong>Donations</strong></td><td>Name, guardian / care-of name, amount, campaign, PAN (used for the 80G receipt), phone and e-mail, an optional note, and the payment references returned by the gateway.</td></tr>
            <tr><td><strong>Mobile app</strong></td><td>The sign-in details you use (membership / volunteer / admin login) and a session token kept in the device&rsquo;s secure storage, plus your language choice. See section 13.</td></tr>
        </tbody>
    </table>
    </div>
    <p>We do <strong>not</strong> collect your card number, CVV, UPI PIN, net-banking credentials, precise location, contacts, microphone or camera data, and we do not collect biometric data.</p>
</section>

<section class="legal-section" id="automatic" data-toc="Data collected automatically">
    <h2>3. Data collected automatically</h2>
    <ul>
        <li><strong>Server logs and security records</strong> &mdash; IP address, date and time, pages requested, browser / device type and error information, used to run, secure and troubleshoot the Services.</li>
        <li><strong>Session and preference cookies</strong> &mdash; see section 12.</li>
        <li><strong>Audit trail</strong> &mdash; actions taken by office-bearers on member and volunteer records (who changed what and when) are logged to prevent misuse.</li>
    </ul>
</section>

<section class="legal-section" id="identity" data-toc="Aadhaar &amp; identity documents">
    <h2>4. Aadhaar and other identity documents</h2>
    <p>Membership needs a reliable identity check so that every member is a real, unique person. To do this:</p>
    <ul>
        <li>When you choose Aadhaar verification, you are redirected to the DigiLocker consent flow run through our verification partner <strong>Cashfree Secure ID</strong>. The Aadhaar number you type is held only in an encrypted session while this happens.</li>
        <li>Alternatively you may verify with PAN, Voter ID, Driving Licence or Passport. The number is sent to the same partner for verification.</li>
        <li>We keep the verification outcome, the provider&rsquo;s reference, the name returned, the last four digits of the document and the time of verification, and we keep the Aadhaar number you entered in your membership record for the purposes of preventing duplicate or fake memberships and completing your verification.</li>
        <li>We <strong>never</strong> print your Aadhaar number on membership cards or show it on public pages, and we do not use it for any purpose other than the ones stated here.</li>
        <li>You provide Aadhaar or any other document voluntarily and only for the stated purpose. If you do not wish to use Aadhaar, use one of the other identity options offered on the membership page.</li>
    </ul>
</section>

<section class="legal-section" id="purposes" data-toc="Why we use your data">
    <h2>5. Why we use your data</h2>
    <p>We process personal data only for lawful purposes, on the basis of your consent or other uses that the law permits (for example, complying with tax and accounting laws or responding to a court order):</p>
    <ul>
        <li>to create, verify and administer memberships, volunteer enrolments, wing registrations and ID cards;</li>
        <li>to run exams &mdash; applications, hall tickets, results, prizes and certificates;</li>
        <li>to receive donations and fees, confirm payments, issue receipts (including for tax exemption) and keep financial records;</li>
        <li>to send OTPs, confirmations, receipts, status updates and important notices by e-mail, SMS or in the app;</li>
        <li>to answer your enquiries, requests and complaints;</li>
        <li>to assign volunteers to their area of work and let authorised office-bearers coordinate within their own area;</li>
        <li>to keep the Services safe &mdash; preventing fraud, duplicate or fake applications, spam and misuse;</li>
        <li>to meet legal, regulatory and audit obligations, and to establish or defend legal claims.</li>
    </ul>
    <p>We do not use your data for advertising, we do not build behavioural profiles, and we do not make decisions about you solely by automated means.</p>
</section>

<section class="legal-section" id="sharing" data-toc="Who we share data with">
    <h2>6. Who we share your data with</h2>
    <p>We do not sell or rent personal data. We share it only as needed to provide the Services:</p>
    <div class="table-wrap">
    <table>
        <thead><tr><th style="width:26%">Recipient</th><th>What and why</th></tr></thead>
        <tbody>
            <tr><td>Razorpay</td><td>Payment processing for membership fees and donations (payer name, contact, amount, payment details you enter on the gateway page).</td></tr>
            <tr><td>Cashfree Payments</td><td>Payment processing for donations and exam fees, and <strong>Cashfree Secure ID</strong> for identity verification (Aadhaar via DigiLocker, PAN, Voter ID, Driving Licence, Passport).</td></tr>
            <tr><td>DigiLocker / UIDAI</td><td>Government of India services used, through Cashfree, to verify Aadhaar with your explicit consent.</td></tr>
            <tr><td>Fast2SMS and our e-mail delivery provider</td><td>Delivering OTPs, confirmations, receipts and notices to your phone or e-mail.</td></tr>
            <tr><td>Amazon Web Services (cloud hosting)</td><td>Hosting the website, database and file storage on which your data is kept.</td></tr>
            <tr><td>Content delivery providers (jsDelivr, Google Fonts)</td><td>Delivering site styling and fonts; your IP address and browser details reach them when a page loads.</td></tr>
            <tr><td>ABVHPS office-bearers and volunteers</td><td>Authorised administrators and area coordinators see only the records their role requires (for example, a mandal coordinator sees volunteers of that mandal). They are bound to use them only for ABVHPS work.</td></tr>
            <tr><td>Authorities and advisers</td><td>Government bodies, courts, police and tax authorities when legally required, and our auditors, chartered accountants and legal advisers under confidentiality duties.</td></tr>
        </tbody>
    </table>
    </div>
    <p>These providers act on our instructions or under their own regulated terms. When they process data as independent regulated entities (for example, payment gateways and banks), their own privacy policies also apply.</p>
</section>

<section class="legal-section" id="public" data-toc="Information shown publicly">
    <h2>7. Information that is visible to the public</h2>
    <p>Some information is shown publicly by design, to give the public confidence that our people and programmes are genuine:</p>
    <ul>
        <li><strong>Public verification pages</strong> (opened when a membership or volunteer QR code or ID is checked) show only the name, ID number, status, cadre and general location.</li>
        <li><strong>Our Team</strong> page lists approved volunteers and office-bearers with the details they have agreed to display (name, role, area and photograph).</li>
        <li><strong>Exam results and winners</strong> &mdash; results are looked up by the applicant&rsquo;s own details. Names, ranks, prizes and photographs of winners may be featured on the winners wall, with the consent given by the applicant or the parent / guardian in the exam application.</li>
        <li><strong>Donations</strong> are never published with your name unless you ask us to.</li>
    </ul>
    <p>If you want something removed from a public page, write to us (see <a href="{{ route('legal.account_deletion') }}">Account &amp; Data Deletion</a>) and we will act on it promptly.</p>
</section>

<section class="legal-section" id="retention" data-toc="How long we keep data">
    <h2>8. How long we keep your data</h2>
    <p>We keep personal data only as long as it is needed for the purpose it was collected for, or as the law requires.</p>
    <div class="table-wrap">
    <table>
        <thead><tr><th style="width:34%">Data</th><th>Retention</th></tr></thead>
        <tbody>
            <tr><td>Enquiries and messages</td><td>Up to 2 years after the matter is closed.</td></tr>
            <tr><td>Membership, volunteer and wing records</td><td>While you are a member / volunteer and for up to 3 years afterwards for verification, disputes and audit.</td></tr>
            <tr><td>Bank and nominee details</td><td>As long as required for the purpose, and removed or anonymised within 12 months after you leave unless the law requires us to keep them.</td></tr>
            <tr><td>Donation, fee and receipt records</td><td>At least 8 years, as required for accounting and income-tax purposes.</td></tr>
            <tr><td>Exam applications and results</td><td>Up to 3 years after the result; a permanent record of prize winners may be kept for history.</td></tr>
            <tr><td>OTPs and sessions</td><td>OTPs expire within minutes; sessions end after a short period of inactivity.</td></tr>
            <tr><td>Server and security logs</td><td>Up to 12 months.</td></tr>
            <tr><td>Privacy requests and our response</td><td>3 years, as proof that we handled the request.</td></tr>
        </tbody>
    </table>
    </div>
    <p>When data is no longer needed we delete it or irreversibly anonymise it. Records that the law obliges us to keep (such as donation accounts) are retained for the required period even if you ask for deletion, and are used only for that purpose.</p>
</section>

<section class="legal-section" id="security" data-toc="How we protect data">
    <h2>9. How we protect your data</h2>
    <p>We use reasonable security safeguards appropriate to the data we hold, including:</p>
    <ul>
        <li>encrypted connections (HTTPS) between your device and our servers;</li>
        <li>OTP verification for mobile and e-mail, and hashed (never plain-text) passwords;</li>
        <li>role-based access, so office-bearers see only what their role needs, plus an audit log of changes to records;</li>
        <li>signature checks on payment-gateway notifications so that a payment cannot be forged;</li>
        <li>security headers and protection against common web attacks, and limiting of repeated requests.</li>
    </ul>
    <p>No system is completely secure, so we cannot promise absolute security. Please protect your own login details, do not share OTPs with anyone &mdash; <strong>ABVHPS staff will never ask for your OTP, PIN or password</strong> &mdash; and tell us immediately if you suspect misuse.</p>
</section>

<section class="legal-section" id="children" data-toc="Children &amp; students">
    <h2>10. Children and students</h2>
    <p>Exam applicants are often under 18. For them, the application must be made by, or with the knowledge and consent of, a parent or legal guardian, whose details we collect on the form. By submitting a child&rsquo;s application, the parent / guardian confirms this consent, including the publication of a winner&rsquo;s name, rank and photograph if the child wins.</p>
    <p>We do not track or monitor the behaviour of children, do not show them advertising, and do not process their data in a way likely to harm them. A parent or guardian can ask us at any time to correct or delete a child&rsquo;s data or to withdraw the consent. The Services are not directed at children for any purpose other than exams and youth programmes run with guardian involvement.</p>
</section>

<section class="legal-section" id="rights" data-toc="Your rights">
    <h2>11. Your rights</h2>
    <p>Under the DPDP Act and other applicable law you have the right to:</p>
    <ul>
        <li><strong>know</strong> what personal data we hold about you, why, and with whom we have shared it, and obtain a summary;</li>
        <li><strong>correct, complete or update</strong> inaccurate or incomplete data;</li>
        <li><strong>erase</strong> your data when it is no longer needed for the purpose or you withdraw consent (subject to the legal-retention exceptions in section 8);</li>
        <li><strong>withdraw consent</strong> at any time, as easily as you gave it. This does not affect what we did lawfully before, and may mean we can no longer provide the related service (for example, a membership that needs verified identity);</li>
        <li><strong>grievance redressal</strong> &mdash; contact our Grievance Officer first; and</li>
        <li><strong>nominate</strong> another person to exercise your rights if you die or become unable to do so.</li>
    </ul>
    <p><strong>How to use these rights:</strong> use the form on <a href="{{ route('legal.account_deletion') }}">Account &amp; Data Deletion</a> or write to <a href="mailto:{{ $officer['email'] }}">{{ $officer['email'] }}</a>. We may ask you to confirm your identity so that nobody else can obtain or delete your data. We acknowledge requests within {{ $legal['acknowledge_days'] }} working days and complete them within {{ $legal['resolve_days'] }} days.</p>
    <p>If you are not satisfied with our response, you may complain to the <strong>Data Protection Board of India</strong> as provided in the DPDP Act, after first raising it with us.</p>
</section>

<section class="legal-section" id="cookies" data-toc="Cookies">
    <h2>12. Cookies and similar technologies</h2>
    <p>We use only the cookies the Services need to work. We do not use advertising cookies or third-party analytics or tracking pixels.</p>
    <div class="table-wrap">
    <table>
        <thead><tr><th style="width:30%">Cookie / storage</th><th>Purpose</th></tr></thead>
        <tbody>
            <tr><td>Session cookie</td><td>Keeps you signed in and remembers your progress in multi-step forms (for example OTP and payment steps).</td></tr>
            <tr><td>Security (CSRF) token</td><td>Protects forms from forged submissions.</td></tr>
            <tr><td><code>abvhps_lang</code></td><td>Remembers the language you chose, for one year.</td></tr>
            <tr><td>Payment gateway cookies</td><td>Set by Razorpay / Cashfree on their own pages to complete and secure your payment.</td></tr>
        </tbody>
    </table>
    </div>
    <p>You can block or delete cookies in your browser, but forms, sign-in and payments will then not work properly. If we later add analytics or other optional cookies, we will update this policy and ask for your consent where the law requires.</p>
</section>

<section class="legal-section" id="app" data-toc="Mobile app">
    <h2>13. The ABVHPS mobile app</h2>
    <ul>
        <li>The app requests only the <strong>Internet</strong> permission. It does not access your contacts, SMS, call log, location, camera, microphone, photos or files.</li>
        <li>Your session token and language preference are stored on your device (the token in the platform&rsquo;s secure storage) and are removed when you sign out or uninstall the app.</li>
        <li>The app shows the same information and uses the same servers as the website, so this policy applies equally to it. It contains no advertising and no third-party analytics.</li>
        <li>You can delete your account and data from within our <a href="{{ route('legal.account_deletion') }}">Account &amp; Data Deletion</a> page, which the app links to.</li>
    </ul>
</section>

<section class="legal-section" id="links" data-toc="Other websites">
    <h2>14. Links to other websites</h2>
    <p>The Services link to other sites, such as our Janavedika page and social-media pages (Facebook, Instagram, YouTube, X, WhatsApp and others) and payment gateways. We do not control them and are not responsible for their privacy practices. Please read their policies.</p>
</section>

<section class="legal-section" id="transfers" data-toc="Data stored outside India">
    <h2>15. Storage and transfers</h2>
    <p>Our cloud providers may store or process data on servers in the region we configure. Where personal data is transferred outside India, it is done only to the extent permitted by the DPDP Act and not to any country that the Government of India has restricted.</p>
</section>

<section class="legal-section" id="breach" data-toc="Data breaches">
    <h2>16. If something goes wrong</h2>
    <p>If a personal-data breach occurs, we will take immediate steps to contain it, and will inform the Data Protection Board of India and the people affected in the manner and time required by law.</p>
</section>

<section class="legal-section" id="changes" data-toc="Changes to this policy">
    <h2>17. Changes to this policy</h2>
    <p>We may update this policy when our practices or the law change. The version and dates at the top show when it last changed. For material changes we will give clear notice on the website or the app, and seek fresh consent where the law requires it. This English version is the governing version.</p>
</section>
@endsection
