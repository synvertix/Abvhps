# ABVHPS app — Google Play submission: legal & policy checklist

_Prepared 21 Sep 2026 from the actual code base. Answers below reflect what the app and server do **today**; re-check them whenever a feature changes. This is a working document for the team, not legal advice — have your legal adviser confirm before submission._

## 1. URLs to paste into Play Console

| Play Console field | URL |
|---|---|
| **Privacy policy** (App content → Privacy policy) | `https://abvhps.org/privacy-policy` |
| **Account deletion** (Data safety → "Delete account URL") | `https://abvhps.org/account-deletion` |
| Terms of service (store listing / support) | `https://abvhps.org/terms-and-conditions` |
| Website | `https://abvhps.org` |
| Support e-mail | `info@abvhps.org` |

* The privacy policy is a normal web page (not a PDF), is public without login, and is also linked **inside the app** (drawer → *Legal & Policies*, and on the sign-in screen).
* The account-deletion page lets a user request deletion **without installing the app**, as Play requires, and the app links to it (drawer → *Delete my account / data*). Requests land in the admin "Contact Messages" desk (source `DATA_REQUEST`) and are e-mailed to the organisation.
* ⚠️ These URLs work only after this branch is deployed to production. Open all of them in a logged-out browser before submitting.

## 2. Permissions
Only `android.permission.INTERNET` is declared (main manifest). No location, contacts, SMS, camera, microphone, storage or notification permissions → nothing to justify in the permissions declaration.

## 3. Data safety form — suggested answers

**Does the app collect or share required user data types?** Yes.
**Is all data encrypted in transit?** Yes (HTTPS; production API is `https://…`).
**Do you provide a way for users to request data deletion?** Yes → account deletion URL above.

### Data collected (via the website/app forms and login)

| Play data type | Collected | Shared | Purpose | Optional? |
|---|---|---|---|---|
| Name | Yes | With payment/verification processors only (see below) | App functionality, Account management | Required for membership / volunteer / exam / donation |
| Email address | Yes | Email delivery provider | App functionality, Account management, Communications | Required |
| Phone number | Yes | SMS provider (OTP) | App functionality, Account management, Fraud prevention | Required |
| Physical address | Yes | No | App functionality (membership, exams) | Required for those forms |
| User IDs | Yes (membership / volunteer IDs) | No | Account management | Required |
| Other personal info (date of birth, gender, blood group, gotram, occupation, nominee, guardian) | Yes | No | App functionality | Required for those forms |
| Financial info → Payment info | **Not collected by us** — card / UPI / bank details are entered on Razorpay / Cashfree pages | Processed by Razorpay / Cashfree | App functionality (payments) | — |
| Financial info → Purchase history | Yes (donation & fee records) | No | App functionality, Legal/accounting | Required |
| Photos | Yes (member/exam photograph — uploaded via the **website** forms) | No | App functionality | Required for those forms |
| Files and docs | Yes (identity, bank, health, family-consent documents — uploaded via website forms) | No | App functionality, Fraud prevention | Required for those forms |
| Government ID numbers (Aadhaar, PAN, Voter ID, DL, Passport) | Yes | Cashfree Secure ID / DigiLocker (verification) | Fraud prevention, Account management | Required for membership verification |
| App activity / diagnostics | Server logs (IP address, timestamps) | No | Security, fraud prevention | — |

* **Not collected:** precise/approximate location, contacts, messages, audio, video, calendar, health & fitness data recorded by the app, web browsing history, installed apps.
* **Advertising / analytics SDKs:** none. No third-party analytics or ad IDs.
* **Data sold?** No.
* **Processors ("service providers") to disclose as *shared for app functionality*:** Razorpay, Cashfree Payments (payments + Secure ID verification), DigiLocker/UIDAI via Cashfree, Fast2SMS (OTP SMS), the e-mail delivery provider, Amazon Web Services (hosting). Under Play's rules, transfers to service providers that process data on your behalf do not have to be declared as "sharing" — confirm with your adviser; declaring them is the conservative choice.

> The Rudrasena **health-fitness declaration** is an uploaded document (website form), not health data measured by the app. Confirm with your adviser whether you want to tick "Health info" for it.

## 4. Other App content declarations
* **Target audience:** 18 and over (membership, volunteering, donations). The exam feature involves students but applications are made by a parent/guardian — choose "18+" / not designed for children. Do **not** join the Families programme.
* **Ads:** No ads.
* **Financial features:** The app links to donation and fee payments made through licensed gateways (Razorpay, Cashfree). Declare it as a charity / non-profit; keep the 80G / 12A certificates available at `https://abvhps.org/compliance-certificates`. If Play asks for non-profit proof, use the registration documents (Registration No. 20/2023).
* **Government apps:** Not a government app — but the app uses the word "Aadhaar" only for verification; do not use UIDAI/Aadhaar logos in the listing.
* **Content rating:** Complete the questionnaire truthfully (religious content, no violence, no user-generated public content).
* **Data deletion & retention:** Answer consistently with Privacy Policy §8 (retention table) and Account & Data Deletion §3.

## 5. Store listing text to keep consistent
* Helpline in listing: **+91 9989980055** (the only public number).
* Do not claim guaranteed tax benefits — say "80G receipts for eligible donations".
* Mention languages: English, हिन्दी, తెలుగు, தமிழ், ಕನ್ನಡ, മലയാളം, मराठी, বাংলা, ગુજરાતી, ଓଡ଼ିଆ, ਪੰਜਾਬੀ.

## 6. Things the organisation must do for the published policies to be true
1. Set the **Grievance Officer's name** (`LEGAL_GRIEVANCE_OFFICER_NAME` in `.env`; optionally `LEGAL_GRIEVANCE_EMAIL`).
2. Actually **process data requests** within the stated times (acknowledge 3 working days, complete 30 days) — requests arrive in Admin → Contact Messages with subject `[DATA REQUEST] …`.
3. Follow the **retention periods** in the Privacy Policy (financial records ≥ 8 years; logs ≤ 12 months, etc.) or edit the policy to match what you will really do.
4. Confirm the **Refund & Cancellation Policy** matches how the treasurer will really handle refunds (they are processed manually from the Razorpay / Cashfree dashboards).
5. Keep the 80G / 12A certificates on `/compliance-certificates` **current**; the policies point there and do not quote registration numbers or validity dates.
6. Have a lawyer / chartered accountant review the jurisdiction (Kadapa, Andhra Pradesh), the FCRA wording, and the 80G statements before publishing.
7. Review the open security items listed in the hand-over notes (documents stored on a public disk, full Aadhaar number stored) — until fixed, the policy deliberately does **not** promise encryption at rest or private storage.
