@extends('legal.layout', [
    'docTitle' => 'Terms & Conditions',
    'docDescription' => 'The rules for using the ' . config('abvhps.legal.short') . ' website and mobile app, joining as a member or volunteer, applying to our wings, taking our exams and paying us.',
])

@section('summary')
    <ul class="!my-0 space-y-1.5 text-[14px]">
        <li>Membership, volunteering, wings and exams are <strong>voluntary</strong>. Volunteering is unpaid service, not employment.</li>
        <li>Give true information. Fake details or documents lead to cancellation and, where needed, legal action.</li>
        <li>Fees and donations are governed by our <a href="{{ route('legal.refund') }}">Refund &amp; Cancellation Policy</a> and <a href="{{ route('legal.donation_payments') }}">Donation &amp; Payment Policy</a>.</li>
        <li>Your data is handled under our <a href="{{ route('legal.privacy') }}">Privacy Policy</a>.</li>
        <li>These terms are governed by Indian law.</li>
    </ul>
@endsection

@section('legal')
<section class="legal-section" id="acceptance">
    <h2>1. Acceptance of these terms</h2>
    <p>These Terms &amp; Conditions (&ldquo;Terms&rdquo;) are an agreement between you and <strong>{{ $legal['entity'] }}</strong> ({{ $legal['registration'] }}), &ldquo;ABVHPS&rdquo;. They apply when you visit <a href="{{ $legal['website'] }}">{{ parse_url($legal['website'], PHP_URL_HOST) }}</a>, use the ABVHPS mobile app, submit any form, make a payment or otherwise use our services (the &ldquo;Services&rdquo;). If you do not agree, please do not use the Services.</p>
    <p>Some activities have their own declarations, oaths or charters (for example the membership disclaimer or the organic-farming oath). These are additional to, and are read together with, these Terms.</p>
</section>

<section class="legal-section" id="eligibility" data-toc="Who may use the Services">
    <h2>2. Who may use the Services</h2>
    <ul>
        <li>You must be at least 18 years old, and legally able to enter into a contract, to become a member, volunteer or wing participant, or to make a donation in your own name.</li>
        <li>People under 18 may take part only in activities meant for students (such as exams) and only with the knowledge and consent of a parent or legal guardian, who is responsible for the application.</li>
        <li>You must give accurate, current and complete information and keep it up to date.</li>
    </ul>
</section>

<section class="legal-section" id="about-abvhps" data-toc="About ABVHPS">
    <h2>3. About ABVHPS and the information we publish</h2>
    <p>ABVHPS is a charitable trust working to protect and promote Sanatana Dharma, build and care for temples and Goshalas, provide Annapurna meals and support education, health and rural development. Information on the Services (including project details, campaign targets, events and exam schedules) is provided in good faith and may change without notice. It is general information, not professional advice.</p>
</section>

<section class="legal-section" id="accounts" data-toc="Accounts &amp; security">
    <h2>4. Accounts, OTPs and passwords</h2>
    <ul>
        <li>You are responsible for everything done through your mobile number, e-mail, OTP, volunteer login or membership ID. Keep them confidential and never share an OTP.</li>
        <li>Tell us at once if you suspect unauthorised use. We may suspend or reset an account to protect you or the organisation.</li>
        <li>Volunteer accounts are created by ABVHPS. You must change the initial password when asked to.</li>
    </ul>
</section>

<section class="legal-section" id="membership" data-toc="Membership">
    <h2>5. Membership</h2>
    <ul>
        <li>Membership is open to persons who accept ABVHPS&rsquo;s objectives, who follow and respect Sanatana (Hindu) Dharma, and who agree to abide by the rules and regulations of the organisation, as confirmed in the application.</li>
        <li>You apply voluntarily, without force or pressure from anyone. Membership is completed only after payment of the membership fee shown at checkout, verification of your mobile number and identity, and submission of the application.</li>
        <li>ABVHPS may accept, hold or reject an application, and may verify any information or document you give.</li>
        <li>Your membership ID and card belong to ABVHPS and are personal to you. They cannot be transferred, sold, copied or used to represent ABVHPS or to collect money in its name.</li>
        <li>We may suspend or cancel a membership, without refund, if information or documents are found to be false or forged, if you break these Terms or the rules of the organisation, or if you act in a way that harms ABVHPS or its purposes. We reserve the right to take civil or criminal action in cases of fraud or impersonation.</li>
        <li>Fees are dealt with under the <a href="{{ route('legal.refund') }}">Refund &amp; Cancellation Policy</a>. A membership fee is not a donation.</li>
    </ul>
</section>

<section class="legal-section" id="volunteers" data-toc="Volunteers &amp; wings">
    <h2>6. Volunteers and wings (Rudrasena, Kala Brundam, Grama Seva Dal, Organic Farmers)</h2>
    <ul>
        <li><strong>Voluntary, unpaid service.</strong> You serve of your own free will and without expecting commercial gain. Volunteering or joining a wing does not create an employer&ndash;employee, agency or partnership relationship, and gives you no right to a salary, wages or any benefit, unless ABVHPS confirms a specific benefit to you in writing.</li>
        <li><strong>Approval and assignment.</strong> Applications are subject to approval. ABVHPS decides your role, cadre, area and duties and may change or end them at any time.</li>
        <li><strong>Conduct.</strong> Serve with honesty, discipline and respect for people and the law. Do not misuse your position or ID, collect money or make commitments in ABVHPS&rsquo;s name without written authority, or discriminate against, harass or endanger anyone.</li>
        <li><strong>Declarations and documents.</strong> You confirm that your bank, nominee, identity and other documents are genuine. False or forged documents lead to immediate removal and possible legal action. Bank and nominee details are used only for administrative purposes connected with your service.</li>
        <li><strong>Rudrasena and physical activities.</strong> Where an activity involves physical effort or risk, you confirm that you are fit to take part (as stated in your health declaration) and that you and your family have consented. You take part at your own risk to the extent the law allows, and you must follow safety instructions.</li>
        <li><strong>Kala Brundam and Grama Seva Dal.</strong> The team or village leader is responsible for the accuracy of the member list and for obtaining each member&rsquo;s consent to be listed.</li>
        <li><strong>Organic Farmers.</strong> You undertake to follow the organic-farming oath. ABVHPS may ask for evidence and may remove you from the programme if the oath is not followed. Registration is not a government or third-party organic certification.</li>
        <li><strong>Ending service.</strong> You may leave at any time by informing us. ABVHPS may end your service for breach of these Terms, misconduct or if it is no longer needed. You must return any ID card or property when asked.</li>
    </ul>
</section>

<section class="legal-section" id="exams" data-toc="Exams">
    <h2>7. Exams</h2>
    <ul>
        <li>Each exam has its own notice (eligibility, syllabus, fee, dates and rules) on the Exams Notice Board. Read it before applying.</li>
        <li>The application must be made truthfully. A parent or guardian is responsible for a minor&rsquo;s application and confirms the consent described in the Privacy Policy, including publication of a winner&rsquo;s name, rank and photograph.</li>
        <li>The application fee is payable online and is dealt with under the <a href="{{ route('legal.refund') }}">Refund &amp; Cancellation Policy</a>. An application is complete only after the fee is confirmed.</li>
        <li>Unfair means, impersonation, copying or misconduct lead to disqualification. Results, ranks and prizes are decided by ABVHPS in accordance with the exam rules and its decision is final, subject to any right you have under the law.</li>
        <li>Schedules, venues and formats may change; we will publish changes on the website. Prizes and recognitions are at ABVHPS&rsquo;s discretion as announced for that exam.</li>
    </ul>
</section>

<section class="legal-section" id="payments" data-toc="Payments &amp; donations">
    <h2>8. Payments and donations</h2>
    <p>Payments are made through third-party payment gateways as explained in the <a href="{{ route('legal.donation_payments') }}">Donation &amp; Payment Policy</a>. Donations are voluntary gifts for charitable purposes and give you no goods or services in return. Membership, volunteer and exam fees are not donations. Refunds are governed only by the <a href="{{ route('legal.refund') }}">Refund &amp; Cancellation Policy</a>.</p>
</section>

<section class="legal-section" id="acceptable-use" data-toc="Acceptable use">
    <h2>9. Acceptable use</h2>
    <p>When using the Services you must not:</p>
    <ul>
        <li>give false information, use someone else&rsquo;s identity, documents or payment instrument, or submit duplicate applications;</li>
        <li>break the law, or upload anything unlawful, defamatory, hateful, obscene or that infringes another&rsquo;s rights or contains malicious code;</li>
        <li>attempt to gain unauthorised access, probe or test security, overload, scrape or copy the Services, or interfere with payments;</li>
        <li>use member or volunteer information you can see for any purpose other than ABVHPS work, or contact people in ways they have not agreed to;</li>
        <li>use the ABVHPS name, logo or ID cards to raise funds, mislead people or suggest an endorsement without our written permission.</li>
    </ul>
</section>

<section class="legal-section" id="content" data-toc="Content &amp; intellectual property">
    <h2>10. Content and intellectual property</h2>
    <p>The ABVHPS name, emblem, logo, text, images, videos and design belong to ABVHPS or its licensors. You may view them and share links for non-commercial purposes but may not copy, modify or use them commercially or in a misleading way without written permission.</p>
    <p>If you upload content (such as photographs and documents), you confirm you have the right to do so and you allow ABVHPS to use it for the purpose for which you gave it, as described in the Privacy Policy.</p>
</section>

<section class="legal-section" id="third-parties" data-toc="Third-party services">
    <h2>11. Third-party services and links</h2>
    <p>Payments, identity verification, SMS, e-mail and hosting are provided by third parties (see the Privacy Policy). Their own terms apply to their services, and we are not responsible for their acts, delays or downtime. Links to other sites are provided for convenience; we do not control or endorse them.</p>
</section>

<section class="legal-section" id="disclaimer" data-toc="Disclaimers">
    <h2>12. Disclaimers</h2>
    <ul>
        <li>The Services are provided &ldquo;as is&rdquo; and &ldquo;as available&rdquo;. We work to keep them accurate and available but do not promise they will be uninterrupted, error-free or free of harmful components.</li>
        <li>Whether a payment qualifies for a tax benefit depends on the law and on your own circumstances. Please consult your tax adviser. See the Donation &amp; Payment Policy.</li>
        <li>Nothing on the Services is legal, medical, financial or tax advice.</li>
        <li>Mention of an event, project or target is not a guarantee that it will happen or be completed as described.</li>
    </ul>
</section>

<section class="legal-section" id="liability" data-toc="Limitation of liability">
    <h2>13. Limitation of liability and indemnity</h2>
    <p>To the extent the law allows, ABVHPS, its trustees, office-bearers and volunteers are not liable for indirect, incidental or consequential loss, or for loss caused by events beyond our reasonable control (such as network or payment-gateway failures, or natural events). Our total liability to you for any claim relating to the Services will not exceed the amount you paid to us for the specific service in question. Nothing in these Terms limits liability that cannot be limited by law, or your rights as a consumer.</p>
    <p>You agree to compensate ABVHPS for loss caused by your breach of these Terms or by false information or documents you provide.</p>
</section>

<section class="legal-section" id="suspension" data-toc="Suspension &amp; termination">
    <h2>14. Suspension and termination</h2>
    <p>We may suspend or end your access, membership, enrolment or application at any time if you break these Terms, provide false information, or if required by law. Sections that by their nature should continue (including declarations, disclaimers, liability and governing law) survive termination.</p>
</section>

<section class="legal-section" id="app-terms" data-toc="Mobile app">
    <h2>15. Mobile app</h2>
    <p>ABVHPS grants you a personal, non-exclusive, non-transferable licence to use the app for its intended purpose on your own device. Do not reverse engineer, copy or redistribute it. We may update or discontinue the app or features; some updates may be required to keep using it. The app store you download it from has its own terms, which apply to that download.</p>
</section>

<section class="legal-section" id="law" data-toc="Governing law &amp; disputes">
    <h2>16. Governing law and disputes</h2>
    <p>These Terms are governed by the laws of India. Please contact our Grievance Officer first; most issues can be solved that way. Any dispute that cannot be settled will be subject to the exclusive jurisdiction of the courts at <strong>{{ $legal['jurisdiction'] }}</strong>, without affecting any right you have to approach a consumer forum or other authority that the law gives you.</p>
</section>

<section class="legal-section" id="general" data-toc="General">
    <h2>17. General</h2>
    <ul>
        <li><strong>Changes.</strong> We may update these Terms. The version and dates at the top show the latest one, and continued use after a change means you accept it. For material changes we will give notice on the website or app.</li>
        <li><strong>Severability.</strong> If any part is found unenforceable, the rest stays in force.</li>
        <li><strong>No waiver.</strong> Not enforcing a right at once does not mean we give it up.</li>
        <li><strong>Entire agreement.</strong> These Terms, the Privacy Policy, the Refund &amp; Cancellation Policy, the Donation &amp; Payment Policy and any form-specific declarations are the whole agreement between you and ABVHPS on this subject. The English version governs.</li>
    </ul>
</section>
@endsection
