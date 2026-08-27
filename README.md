# AKHANDA BHARATHA VISWA HINDU PARIRAKSHANA SAMITI (ABVHPS)
### Central Digital Governance, Devotee Administration & Seva Platform

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://php.net)
[![Laravel Framework](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Vite](https://img.shields.io/badge/Vite-7.x-646CFF?logo=vite&logoColor=white)](https://vitejs.dev)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.x-38B2AC?logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Test Suite](https://img.shields.io/badge/Tests-365%20Passed%20(1783%20Assertions)-success?logo=phpunit&logoColor=white)](tests/)
[![Security Hardened](https://img.shields.io/badge/Security-Hardened%20%26%20Zero--PII-blue?logo=shield&logoColor=white)](#-security-hardening-matrix)
[![License](https://img.shields.io/badge/License-Proprietary-orange.svg)](#license)

---

## 📌 Executive Summary

**ABVHPS** (*Akhanda Bharatha Viswa Hindu Parirakshana Samiti*) is an institutional-grade, modern digital governance platform engineered to coordinate socio-cultural initiatives, devotee registries, youth cadre mobilization, multi-tier volunteer administration, examinations & hall ticket verification, local GP gateway wings, volunteer-led community events, secure KYC identity verification, and statutory-compliant Dharma Seva fundraising across India.

---

## 🏛️ Comprehensive Architecture Diagram

```
                                  ┌───────────────────────────────┐
                                  │      Public Devotees & Users  │
                                  └───────────────┬───────────────┘
                                                  │
                 ┌────────────────────────────────┴─────────────────────────────────┐
                 │                                                                  │
     ┌───────────▼───────────┐                                          ┌───────────▼───────────┐
     │  Public Portal Desks  │                                          │  QR Anti-Fraud Engine │
     │  • Life Membership    │                                          │  • /verify/{entity}/  │
     │  • Exam Applications  │                                          │  • Dynamic DB Lookup  │
     │  • Wing Registrations │                                          │  • Zero-PII Exposure  │
     │  • Dharma Seva Ledger │                                          └───────────────────────┘
     └───────────┬───────────┘
                 │
 ┌───────────────▼────────────────┐                             ┌───────────────────────────────┐
 │     Laravel 12 MVC Core        │◄────────────────────────────┤    Multi-Guard Auth Matrix    │
 │     (PHP 8.2+ / Eloquent ORM)  │                             │    • web: Admin Commander     │
 └───────────────┬────────────────┘                             │    • volunteer: 6-Digit ID    │
                 │                                              └───────────────────────────────┘
   ┌─────────────┼─────────────┬─────────────┬─────────────┬─────────────┐
   │             │             │             │             │             │
 ┌─▼────────┐  ┌─▼────────┐  ┌─▼────────┐  ┌─▼────────┐  ┌─▼────────┐  ┌─▼────────┐
 │ Database │  │ Identity │  │ Payment  │  │ TinyProof│  │ AuditLog │  │ Storage  │
 │ MySQL /  │  │ Cashfree │  │ Razorpay/│  │ Image    │  │ Redacted │  │ AWS S3 / │
 │ SQLite   │  │ Secure ID│  │ Cashfree │  │ Engine   │  │ Logs     │  │ Local    │
 └──────────┘  └──────────┘  └──────────┘  └──────────┘  └──────────┘  └──────────┘
```

---

## 🛠️ Complete Technologies & Frameworks Matrix

### 1. Backend & Server-Side Core
- **Language:** **PHP 8.2+** (Strict typing, typed class constants, modern match expressions, readonly properties, fibers).
- **Core Framework:** **Laravel 12.x**
  - **Eloquent ORM:** Advanced model relations, accessors/mutators, custom casts, scopes, and transaction management.
  - **Blade Templating Engine:** Component-driven views, layouts, subviews, directives, slots, and asset helpers.
  - **Service Container & Providers:** Custom decoupled service providers for payment integrations, identity verification, image processing, and PDF rendering.
  - **Routing & Middleware:** Custom security headers, rate limiting, guard enforcement, volunteer credential policies, and CSRF protection.
  - **Mailable & Notifications:** Asynchronous welcome emails, donation receipts, confirmation alerts, and templated HTML notifications.
  - **Validation & Form Requests:** Strict server-side input sanitation, regex pattern constraints, payload byte invariants, and MIME type verifications.
- **Developer & CLI Tooling:**
  - **Laravel Tinker (`laravel/tinker` ^2.10.1):** Interactive REPL for runtime debugging and state inspections.
  - **Laravel Pail (`laravel/pail` ^1.2.2):** Real-time CLI log tailing during local development.
  - **Laravel Pint (`laravel/pint` ^1.24):** Opinionated PHP code style fixer and linter based on PSR-12 and Laravel standards.
  - **Laravel Sail (`laravel/sail` ^1.41):** Docker-powered containerized development environment support.

### 2. Frontend, Styling & UI Engineering
- **Build Tool & Bundler:** **Vite 7.x** (`vite` ^7.0.7) paired with `laravel-vite-plugin` (^2.0.0) for lightning-fast Hot Module Replacement (HMR) and optimized asset compilation.
- **CSS Architecture & Framework:** **Tailwind CSS v4.0.0** (`tailwindcss` & `@tailwindcss/vite` ^4.0.0) with native CSS variables, container queries, and modern fluid layouts.
- **Client-Side Scripting:** **Vanilla ES6+ JavaScript** (Async/Await, Fetch API, FormData, dynamic DOM rendering, event delegation).
- **Micro-Interactivity:** **Alpine.js** for reactive UI interactions (modals, dropdowns, dynamic input masking, and tab switching).
- **HTTP Client:** **Axios 1.11.0** for asynchronous API communication.
- **Client-Side Image Pre-Processing:** HTML5 Canvas API for real-time proof image scaling and downsampling before network dispatch.
- **Typography & Iconography:** Google Fonts (*Outfit, Inter, Rajdhani*) and lightweight inline SVG vector icons.

### 3. Payment Gateways & Financial Infrastructure
- **Razorpay Payments (`RazorpayPaymentService`):**
  - Order creation via REST API.
  - Client-side checkout modal integration.
  - HMAC-SHA256 signature verification (`X-Razorpay-Signature`).
  - Automated donation ledgering and status reconciliation.
- **Cashfree Payments (`CashfreePaymentService`):**
  - Seamless Checkout and Payment Gateway Order API integration.
  - Cryptographic webhook validation (`x-webhook-signature`, `x-webhook-timestamp`).
  - Full support for Sandbox testing and Production transactions.
- **Statutory 80G Donation Ledger:**
  - Automatic tax-deductible receipt generation (`ABVHPS-TXN-XXXXXX`).
  - Real-time campaign tracking, goal progress bars, and social sharing.

### 4. Identity Verification, KYC & Anti-Fraud Engines
- **Cashfree Secure ID Engine (`CashfreeSecureIdService`):**
  - Aadhaar verification workflows (OTP generation, submission & DigiLocker OCR verification).
  - Sandbox mode simulation with mock response verification.
  - Zero-PII storage policy: Raw Aadhaar numbers and private credentials are never stored in plain text.
- **Tiny Proof Image Compression Engine (`TinyProofImageService`):**
  - Ultra-efficient micro-footprint image compression engine maintaining strict `<2KB` model-level invariants for beneficiary proof thumbnails.
  - Automatic base64 and binary image sanitization, format conversion, and tamper-proof storage lifecycle.
- **Dynamic Cryptographic QR Verification (`simplesoftwareio/simple-qrcode` ~4):**
  - Vector SVG and PNG dynamic QR code generation.
  - Embedded validation links to `/verify/{entity}/{id}`.
  - Public verification desk with zero-PII exposure (Aadhaar, email, and phone numbers are strictly suppressed).

### 5. Document Generation & Cloud Media Storage
- **PDF Engine:** **`barryvdh/laravel-dompdf` (^3.1)** and Dompdf engine.
  - High-resolution, vector-accurate PVC Life Membership Cards.
  - Printable Examination Hall Tickets.
  - Multi-page Area Member Rosters.
  - Statutory 80G Tax Exemption Receipts.
- **Cloud Storage:** **AWS S3** via `aws/aws-sdk-php` (^3.0) and `league/flysystem-aws-s3-v3` (^3.0).
- **Local Storage:** Secure, quarantined file storage with symlinked public distribution paths.

### 6. Database & Persistence Layer
- **Production Database:** **MySQL 8.0+** / **MariaDB 10.4+** (InnoDB engine, strict mode, indexed search keys).
- **Testing & Local Database:** **SQLite 3** (High-performance in-memory and isolated file storage).
- **Database Schema Management:** Robust migrations, seeders, foreign keys, cascading rules, and database-level unique constraints.

### 7. Quality Assurance & Automated Testing
- **Test Framework:** **PHPUnit 11.5.50** (`phpunit/phpunit` ^11.5.50).
- **Mocking & Isolation:** **Mockery 1.6** (`mockery/mockery` ^1.6) for external service mock suites (Cashfree, Razorpay, S3, Mailers).
- **Data Factories:** **FakerPHP (`fakerphp/faker` ^1.23)** for realistic test dataset synthesis.
- **Coverage:** **365 Feature and Unit Tests passing with 1,783 assertions**.

---

## 🔱 Key Platform Modules & Workflows

### 1. 💳 Life Membership Management
- **Mobile OTP Authentication:** 2-step SMS verification with automatic 5-minute TTL.
- **12-Digit Unique Key Matrix:** Dynamic unique identification formatting (e.g., `4318 2764 1156`).
- **Aadhaar Identity Verification:** Cashfree Secure ID integration for OTP-based Aadhaar validation.
- **PVC Digital ID Card Desk:** High-resolution digital ID card rendering with embedded dynamic QR codes.
- **Automated Welcome Email:** Instant dispatch of onboarding instructions and member credentials upon verification.
- **Multilingual Support:** Intelligent language auto-assignment (`Telugu`, `Kannada`, `English`).

### 2. 🛡️ Dedicated Volunteer Portal (`/volunteer/login`)
- **6-Digit Login Architecture:** Unique 6-digit login ID and password authentication for verified volunteers.
- **Security & Password Policy:** Mandatory first-login password reset (`must_change_password`), session invalidation on logout, and brute-force throttling (5 attempts/min).
- **Hierarchical Jurisdiction:** Village, Mandal, Assembly Segment, District, State, and Global Overseer control desks.
- **Area Member Explorer:**
  - Cascading area selection (District &rarr; Mandal &rarr; Grama Panchayat).
  - Search and preview up to 100 members in real time.
  - **Secure PDF & CSV Export Desk:** Filtered member rosters containing strictly approved public fields (*Name, Gender, Photo, Membership ID, Region*). **Aadhaar, contact numbers, emails, and database keys are never exposed.**
  - Full audit logging of all export operations.

### 3. 🤝 Volunteer Events & Beneficiary Management (`/volunteer/events`)
- **Event Lifecycle Management:** Volunteers can create, update, and manage community service events (*Annadanam, Medical Camps, Cultural Programs, Environmental Drives*).
- **Beneficiary Roster & Proofs:** Add event beneficiaries, verify their 12-digit membership status, and attach compressed proof images via the `TinyProofImageService`.
- **Admin Event Oversight (`/admin/volunteer-events`):** Central dashboard to audit, review, verify, and monitor volunteer-driven field events across all districts.

### 4. 📝 Examination Portal & Hall Ticket Engine
- **Multi-Exam Lifecycle Management:** Create and schedule multiple examinations with custom exam types (`Theory`, `MCQ`, `Both`), center locations, guidelines, and cash prizes.
- **Exam Syllabus Repository:** Downloadable syllabus PDFs tied directly to each exam setting.
- **Parent Eligibility Gate:** Server-side verification enforcing verified parent/guardian membership IDs before candidate registration.
- **11-Digit Unique Hall Ticket Generator:** Randomized collision-safe 11-digit hall ticket numbers (e.g., `84729103847`).
- **Exam Results & Winners Wall:**
  - Administrative draft result entry (marks, percentage, grade, remarks).
  - One-click bulk publication with multi-channel notification loggers.
  - Public results lookup and Top 6 Winners Showcase Wall.

### 5. ⚔️ Rudra Sena Youth Command (`/rudrasena-apply`)
- Specialized registration for youth commandos and cultural defense forces.
- Unique sequential ID matrix formatting: `RS0001`, `RS0002`, ...
- Administrative dossier view, approval workflow, PDF ID card download, and QR verification.

### 6. 🌾 Local GP Gateway Wings (`/admin/local-gateways`)
- **Organic Farmers Agriculture Wing (`OF-XXXXXX`):** Desi agriculture registration, multi-crop mapping, native cow counts, and digital Green Certification issuance.
- **Kala Brundam Cultural Wing (`KB-XXXXXX`):** Folk artist and cultural team registration, performing arts categorization, and troop strength tracking.
- **Grama Seva Dal Village Wing (`GSD-XXXXXX`):** Grama Panchayat youth service force roster, seva history recording, and identity verification.

### 7. 📜 Dharma Seva Fundraising & Legal Ledger
- Multi-media campaigns supporting high-resolution galleries and field briefing videos.
- Dynamic financial progress bars, real-time targets vs. raised amounts, and social share links.
- Devotee Legal Donation Ledger with automated 80G tax-exemption digital cash receipt PDF generator (`ABVHPS-TXN-XXXXXX`).

### 8. 👑 Central Administrative Control Desk (`/admin/login`)
- Unified commander dashboard with live metric counters across all wings.
- Page-Wise Banner Management Engine with device-aware mobile/desktop image serving.
- Contact message tracker with note-taking and resolution state logs.
- Statutory compliance certificates desk (10AC, 12A, 80G, CSR).
- Volunteer and event approval matrices.

---

## 🆔 Master Identity & Verification Schema

| Entity | ID Format | Example | Storage Key | Public Verification Endpoint |
|---|---|---|---|---|
| **Life Membership** | 12-Digit Numeric | `9224 9312 1520` | Indexed `membership_id` | `/verify/membership/{id}` |
| **Volunteer** | 6-Digit Numeric | `849201` | Indexed `volunteer_login_id` | `/verify/volunteer/{id}` |
| **Rudra Sena Member** | `RS` + 4 Digits | `RS0001` | Indexed `rudrasena_id` | `/verify/rudrasena/{id}` |
| **Exam Hall Ticket** | 11-Digit Numeric | `84729103847` | Indexed `hall_ticket_number` | `/verify/exam/{ticket}` |
| **Organic Farmers Group** | `OF-` + 6 Digits | `OF-583214` | Indexed `unique_id` | `/verify/organic-farmers/{id}` |
| **Kala Brundam Group** | `KB-` + 6 Digits | `KB-194820` | Indexed `unique_id` | `/verify/kala-brundham/{id}` |
| **Grama Seva Dal Group** | `GSD-` + 6 Digits | `GSD-720194` | Indexed `unique_id` | `/verify/grama-seva-dal/{id}` |

---

## 🔒 Security Hardening Matrix

- **HTTP Security Headers:** Comprehensive `SecurityHeaders` middleware injecting:
  - `X-Content-Type-Options: nosniff`
  - `X-Frame-Options: SAMEORIGIN`
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Strict-Transport-Security: max-age=31536000; includeSubDomains` (HTTPS mode)
  - Strict `Content-Security-Policy` protecting against XSS and unauthorized frame embedding.
- **Audit Logging Subsystem:** Centralized logging of all administrative actions, status changes, and data exports with automatic PII sanitization.
- **Rate Limiting:** Granular throttling protecting Admin login (5/min), Volunteer login (5/min), OTP requests, member searches, webhook endpoints, and PDF/CSV export streams.
- **Webhook Cryptographic Security:** Cryptographic validation for Cashfree and Razorpay webhooks (HMAC-SHA256 signature verification, replay prevention via timestamp checks).
- **File Upload Security:** Enforces strict MIME checks, size limits, randomized filenames, storage isolation, and total rejection of executable scripts (`.php`, `.phtml`, `.phar`, `.sh`, `.exe`).
- **Zero-PII Exposure on QR Verification:** Public verification desk validates authenticity, cadre, and active status while strictly suppressing Aadhaar, phone numbers, private emails, and database primary keys.

---

## 🚀 Installation & Local Setup

### Prerequisites
- **PHP `>= 8.2`** with required extensions: `pdo`, `pdo_mysql`, `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `gd`, `fileinfo`, `zip`, `curl`
- **Composer `>= 2.2`**
- **Node.js `>= 18.x`** & **npm**
- **MySQL 8.0+** or **MariaDB** (or **SQLite 3** for local development)

### Step-by-Step Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/Kondareddy1209/Abvhps.git
   cd Abvhps
   ```

2. **Install PHP and Node dependencies:**
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Update `.env` with your database credentials, payment gateway keys, mail settings, and S3 credentials as needed.*

4. **Run Database Migrations and Seeders:**
   ```bash
   php artisan migrate --seed
   ```

5. **Create Storage Symlink:**
   ```bash
   php artisan storage:link
   ```

6. **Build Frontend Assets:**
   ```bash
   npm run build
   # or for active development:
   npm run dev
   ```

7. **Start the Laravel Development Server:**
   ```bash
   php artisan serve
   ```
   Or run full concurrent development mode:
   ```bash
   composer dev
   ```

---

## 🧪 Automated Testing Suite

The application includes an extensive test suite verifying business logic, payment integrations, identity verification, access controls, webhooks, and UI security.

Run the test suite via Artisan:

```bash
php artisan test
```

### Test Suite Metrics:
```
Tests:    365 passed (1783 assertions)
Duration: ~70s
```

### Key Test Coverage Areas:
- `Tests\Feature\AadhaarVerificationAndLogoTest` & `CashfreeSecureIdVerificationTest` (Aadhaar KYC & sandbox verification)
- `Tests\Feature\MembershipRazorpayPaymentTest` & `DonationPaymentIntegrationTest` (Payment orders, verify endpoints, and error handling)
- `Tests\Feature\WebhookSecurityTest` (Cryptographic webhook signature validation)
- `Tests\Feature\VolunteerEventTest` & `AdminVolunteerEventTest` & `VolunteerEventBeneficiaryTest` (Volunteer events & beneficiary lifecycle)
- `Tests\Feature\TinyProofImageServiceTest` (Image compression & byte invariant guarantees)
- `Tests\Feature\VolunteerLoginTest` & `VolunteerMemberDataTest` & `VolunteerMemberSearchTest` (Multi-guard security, data privacy, and area exports)
- `Tests\Feature\MembershipMailTest` & `DonationCertificatesTest` (PDF generation, 80G receipts, and email deliveries)

---

## 📦 Deployment & Git Workflow

When contributing to the repository:

```bash
# 1. Ensure you have the latest changes
git pull origin main

# 2. Run test suite to ensure all tests pass
php artisan test

# 3. Build optimized assets
npm run build

# 4. Commit and push your changes
git add .
git commit -m "feat: your descriptive feature summary"
git push origin main
```

---

## 📄 Statutory Information & Compliance

All statutory registration documentation (including 12A registration, 80G tax exemption, 10AC certificates, and CSR approval records) is accessible on the portal under `/compliance-certificates`.

---

## ⚖️ License & Proprietary Rights

All rights reserved © 2026 **Akhanda Bharatha Viswa Hindu Parirakshana Samiti (ABVHPS)**.  
Unauthorized distribution, replication, or modification of this software platform is strictly prohibited.
