# ABVHPS Website to Flutter Mobile App Parity Audit & Architecture

**Document Version:** 1.0.0  
**Date:** 2026-08-27  
**Audit Scope:** Exhaustive read-only mapping of Laravel Web Application (`c:\xampp\htdocs\abvhps`) to Flutter Mobile App (`mobile_app`).

---

## 1. Executive Summary & Parity Matrix

The ABVHPS Laravel web application is an enterprise platform serving devotees, members, volunteers, and administrators across India. This document establishes the formal audit across all routes, views, models, assets, and APIs, defining a clear tiered roadmap from Web to native Flutter.

| Classification | Count | Description |
|---|---|---|
| **P0 (Critical First Release)** | 9 | Core public & identity experiences: Home, About, Team, Gallery, Blogs, Contact, Membership info/auth, Volunteer auth/dash, Public Donations Info |
| **P1 (Important Next Release)** | 5 | Extended services: 80G Certificates, Project Details, Exam Notice Board & Results, Single Campaign Detail, Wings Info |
| **P2 (Later Release)** | 5 | Complex multi-step dynamic forms (Exam Application, Wing Forms, Dal Registration) |
| **WEB-ONLY** | 8 | Gateway redirects, webhook receivers, public QR verification endpoints, Sitemap |
| **ADMIN-ONLY** | 16 Modules | Administrative dashboards, CRUD actions, batch approvals, system settings |

---

## 2. Complete User-Visible Web Route Audit

| Page Name | URL & Route Name | Controller & View | Auth | Web Mobile Sections | Assets (Static / Dynamic) | DB Model Source | Forms & Actions | Download Behavior | Flutter Priority |
|---|---|---|---|---|---|---|---|---|
| **Home Page** | `/`<br>`public.home` | `HomeController@index`<br>`home.blade.php` | None | Header, Hero Banner, Announcement, Origin & Guru Blessings, Vision/Mission, Join Strip, Stats Counter, Campaigns, Core Projects, Partners Marquee, Social Strip, Footer | Static: Logos, fallback video<br>Dynamic: Banners, Campaigns, Projects, Partner logos | `Banner`, `SiteSetting`, `FundraisingCampaign`, `OurSupport`, `ExamSetting`, `Membership`, `Volunteer`, `Donation` | None | None | **P0** |
| **About Us** | `/about`<br>`about` | `HomeController@about`<br>`about.blade.php` | None | Page Banner, Mission Statement, 4 Core Values (Dharma Rakshana, Nishkama Seva, Grama Vikas, Unity) | Static: Logo, banner fallback<br>Dynamic: Banner image | `Banner`, `SiteSetting` | None | None | **P0** |
| **Our Team / Leadership** | `/team`, `/our-team-members`<br>`public.team` | `HomeController@team`<br>`team.blade.php` | None | Banner, 6-Tier Geographic & Cadre Cascading Filter, Search box, Volunteer Card Grid with Membership details | Static: Background fallback<br>Dynamic: Volunteer photos, banner | `Volunteer`, `Membership`, `Banner` | GET query filter parameters | None | **P0** |
| **Media Gallery** | `/gallery`<br>`public.gallery` | `HomeController@gallery`<br>`gallery.blade.php` | None | Page Banner, Photo & Video Grid with filter/tag badges | Static: `images/gallery-hero.png`<br>Dynamic: Uploaded photos, video URLs | `Gallery`, `Banner` | None | None | **P0** |
| **Blogs & Updates** | `/blogs`<br>`public.blogs` | `HomeController@blogs`<br>`blogs.blade.php` | None | Page Banner, Paginated Article Cards with thumbnails, publication dates, excerpts | Static: Banner fallback<br>Dynamic: Blog thumbnails | `Blog`, `Banner` | None | None | **P0** |
| **Contact Us** | `/contact`<br>`public.contact` | `ContactController@showContactPage`<br>`contact.blade.php` | None | Page Banner, Headquarter Info Card, Direct Contact Form with anti-spam | Static: Icons, banner fallback<br>Dynamic: Settings (`contact_phone`, `contact_email`, `contact_address`) | `ContactMessage`, `SiteSetting`, `Banner` | POST `/contact/submit` | None | **P0** |
| **Fundraise / Donations** | `/donations`<br>`donations.grid` | `FundraisingController@showDonationsGrid`<br>`donations_grid.blade.php` | None | Hero Banner, 5 Pillar Highlights, Active Campaign Grid with Progress Bars, Donation Checkout Form | Static: `images/fundraise_bg.png`<br>Dynamic: Campaign covers, videos | `FundraisingCampaign`, `Banner` | POST Razorpay / Cashfree Initiation (Web only) | Receipt PDF download (Post-payment) | **P0 (Info/Preview)**<br>*Payment: Future Phase* |
| **Campaign Detail** | `/donations/campaign/{id}`<br>`donations.campaign` | `FundraisingController@showCampaign`<br>`donations_grid.blade.php` | None | Single Campaign Hero, Description, Target/Raised tracker, WhatsApp share, Donation Form | Dynamic: Campaign cover & video | `FundraisingCampaign` | Payment Initiation | Receipt PDF | **P1** |
| **Single Project Details** | `/project/{id}`<br>`public.project.show` | `HomeController@showProject`<br>`project_details.blade.php` | None | Project Banner, Main Image, Detailed Text Content, Back navigation | Dynamic: Project image | `OurSupport` | None | None | **P1** |
| **80G & 12A Certificates** | `/compliance-certificates`<br>`public.certificates` | `CertificateController@publicIndex`<br>`compliance_certificates.blade.php` | None | Page Banner, Statutory Certificates Grid with Reg Numbers and validity | Dynamic: PDF files stored in storage | `TaxCertificate`, `Banner` | None | Secure Direct PDF Download | **P1** |
| **Exams Notice Board** | `/exams-notice-board`<br>`public.exams_board` | `ExamController@publicNoticeBoard`<br>`exams_notice_board.blade.php` | None | Page Banner, Cycle status pills, Schedule, Center, Fee, Awards Matrix, Syllabus Link | Dynamic: Syllabus PDFs | `ExamSetting`, `Banner` | None | Syllabus PDF Download | **P1** |
| **Exam Results Portal** | `/exam-results`<br>`exam.results_portal` | `ExamController@showResultsPortal`<br>`exam_results.blade.php` | None | Hall Ticket search bar, Candidate score card, Rank, Certificate download | Dynamic: Hall ticket records | `ExamApplication`, `ExamSetting` | POST `/exam-results/search` | Result Certificate PDF | **P1** |
| **Membership Portal / Login** | `/membership`<br>`membership.form` | `MembershipController@showOtpForm`<br>`membership_otp.blade.php` | Public / Member | Member Login via OTP, Verification, Profile, PVC Card View | Static: `membership_card_bg.png`<br>Dynamic: Member avatar, QR | `Membership` | POST Send OTP / Verify OTP | Printable PVC Card View / PDF | **P0 (Auth & Card)**<br>*Form: P2* |
| **Volunteer Portal / Login** | `/volunteer/login`<br>`volunteer.login` | `VolunteerAuthController@showLoginForm`<br>`volunteer_login.blade.php` | Public / Volunteer | Volunteer Login (ID/Password), Dashboard, Profile, Hierarchy Drill-down | Static: `volunteer_card_bg.png`<br>Dynamic: Volunteer photo, ID card | `Volunteer` | POST Login / Change Password | Volunteer PVC ID Card View | **P0 (Auth & Dash)** |
| **Rudrasena Dal Info & Wing** | `/rudrasena-apply`<br>`rudrasena.form` | `RudrasenaController@showApplicationDesk`<br>`rudrasena_application.blade.php` | None / Member | Wing overview, Eligibility, Application Form for Members | Static: `rudrasena_card_bg.png`<br>Dynamic: Wing media | `RudrasenaMember`, `Membership` | POST Member Verification & Dal Packet Submit | ID Card View | **P0 (Info)**<br>*Form: P2* |
| **Kala Brundam Cultural Wing** | `/kala-brundam-apply`<br>`kalabrundam.form` | `KalaBrundamController@showApplicationDesk`<br>`kala_brundam_application.blade.php` | None / Member | Cultural wing overview, Team member roster form | Static: Branding assets | `Membership` | POST Dal Packet Submit | Group Card View | **P1 (Info)**<br>*Form: P2* |
| **Grama Seva Dal Wing** | `/grama-seva-dal-apply`<br>`gramasevadal.form` | `GramaSevaDalController@showApplicationDesk`<br>`grama_seva_dal_application.blade.php` | None / Member | Youth service wing overview, local Dal formation form | Static: Branding assets | `Membership` | POST Dal Packet Submit | Group Card View | **P1 (Info)**<br>*Form: P2* |
| **Organic Farmers Network** | `/organic-farmers-apply`<br>`organicfarmers.form` | `OrganicFarmerController@showApplicationDesk`<br>`organic_farmer_application.blade.php` | None / Member | Sustainable agriculture wing overview, farmer cluster form | Static: Branding assets | `Membership` | POST Dal Packet Submit | Group Card View | **P1 (Info)**<br>*Form: P2* |

---

## 3. Exact Homepage Section Audit

Inspection of `resources/views/home.blade.php` and `resources/views/layouts/app.blade.php` reveals the exact sequential section order:

```
[1] TOP HEADER (Phone, Email, Social Channels)
        ↓
[2] MAIN NAVIGATION (Logo, 12 Navigation links / Mobile Glass Drawer)
        ↓
[3] HERO BANNER (Admin Banner OR Video/Slider Fallback)
        ↓
[4] ANNOUNCEMENTS STRIP (Published Exam Cycles)
        ↓
[5] DIVINE ORIGIN & GURU BLESSINGS (Sri Sri Sri Subrahmanneswara Swamy Garu)
        ↓
[6] VISION, MISSION & GOALS (3 Pillars)
        ↓
[7] FLOATING JOIN/MEMBERSHIP STRIP ("Why Join ABVHPS" & CTA)
        ↓
[8] LIVE STATISTICS COUNTER (Donors, Members, Volunteers, Years)
        ↓
[9] FUNDRAISING CAMPAIGNS SHOWCASE (Active Causes & Progress Bars)
        ↓
[10] CORE SERVICE PROJECTS (Our Support Modules)
        ↓
[11] SUPPORTING PARTNERS / SPONSORS (Scrolling Marquee Strip)
        ↓
[12] CONNECT WITH ABVHPS (Social Media Platform Links)
        ↓
[13] FOOTER & FLOATING WHATSAPP BUTTON (Quick Links, Wings, Address, Copyright)
```

### Detailed Section Properties

1. **Top Header & Bar**
   - Heading/Text: Phone `+91 8884933379`, Email `info@abvhps.org`
   - Data Source: `SiteSetting::get('contact_phone')`, `SiteSetting::get('contact_email')`, `SiteSetting::getActiveSocialLinks()`
   - Behavior: Responsive top banner.
2. **Main Navigation**
   - Logos: Emblem (`images/logo_abvhps.png`) + Stylized Wordmark (`images/logo.png`)
   - Links: Home, About, Our Team, Gallery, Membership, Volunteer, Exams Submenu, Our Wings Submenu, Fundraise, Blogs, Contact, Login Modal, Donation CTA.
   - Mobile: Hamburger trigger opening 360px glassmorphic drawer with categorized sections.
3. **Hero Banner / Slider**
   - Heading: `Banner::getBannerForPage('home')` title & subtitle OR slider records (`home_sliders`)
   - Asset Source: Dynamic storage path (`storage/...`) or fallback video `videos/hero.mp4`
4. **Announcement Strip**
   - Dynamic Source: `exam_settings` where published applications exist.
   - Action: "View Results →" navigating to `/exam-results`.
5. **Organization Divine Origin & Blessings**
   - Heading: "Why and How ABVHPS Was Founded" / "Divine Blessings"
   - Text Source: Static template text referencing Registration Number 20/2023 and Rajaguru Sri Sri Sri Subrahmanneswara Swamy Garu.
6. **Vision, Mission, Goal**
   - 3 Feature Cards: Our Vision, Our Mission, The Goal.
7. **Floating Membership / Join Strip**
   - Headings & Text: `SiteSetting::get('homepage_join_why_heading')`, `SiteSetting::get('homepage_join_why_text')`, `SiteSetting::get('homepage_join_member_heading')`
   - CTA: "BECOME A MEMBER" -> `/membership`
8. **Live Statistics Strip**
   - Live DB Counts (Cached 10 mins): Verified Donors, Registered Members, Approved Volunteers, Years of Service (`SiteSetting::get('years_of_service')`).
9. **Fundraising Campaigns Showcase**
   - Data: `FundraisingCampaign::active()->take(6)`
   - Properties: Cover image, title, short description, raised amount, target amount, % progress bar, WhatsApp Share link, Contribute CTA.
10. **Core Service Projects**
    - Data: `our_supports` where `status='show'`
    - Properties: Project image, name, short info, "Explore Project →" CTA.
11. **Supporting Partners / Sponsors Marquee**
    - Data: `SiteSetting::getSupportingPartners()`
    - Properties: Partner names & logos rendered in continuous marquee.
12. **Connect With ABVHPS Strip**
    - Data: `SiteSetting::getActiveSocialLinks()`
    - Buttons: Facebook, Instagram, YouTube, X, LinkedIn, WhatsApp, Telegram with official brand colors.
13. **Footer & Quick Connect**
    - Columns: About ABVHPS, Quick Links, Our Wings, Services & Exams, Contact Address, WhatsApp floating quick connect button.

---

## 4. Asset Inventory & Strategy

| Category | Assets | App Placement / Delivery |
|---|---|---|
| **Permanent Branding** | `logo_abvhps.png`, `logo.png`, `ABVHPS_LOGO.jpg`, default placeholders | Bundled in `mobile_app/assets/images/` |
| **Admin Dynamic Media** | Banners (`storage/banners/*`), Campaign covers (`storage/campaigns/*`), Project photos (`storage/projects/*`), Gallery media (`storage/gallery/*`), Volunteer photos (`storage/volunteers/*`), Partner logos | Served via Laravel public URLs (`https://abvhps.org/storage/...` or emulator `http://10.0.2.2:8000/storage/...`) |
| **Private User Content** | Aadhaar proofs, PAN cards, Voter IDs, DL cards, internal event proofs | **Strictly 0 in Flutter bundle**. Access restricted behind Sanctum authentication. |
| **Downloadable Documents** | 80G/12A PDFs, Exam Syllabus PDFs, Member PVC Cards, Volunteer Cards | Delivered via authenticated or signed public API endpoints. |

---

## 5. Mobile API Design Specification (/api/v1/)

All endpoints follow RESTful standards with envelope responses:
```json
{
  "success": true,
  "data": { ... },
  "meta": { "timestamp": 1724760000 }
}
```

### Public API Endpoints:
1. `GET /api/v1/home` — Aggregated homepage payload (banners, stats, preview campaigns, core projects, sponsors, social links, join strip, announcements).
2. `GET /api/v1/about` — Organization mission, vision, core values, and Guru Garu message.
3. `GET /api/v1/campaigns` & `GET /api/v1/campaigns/{id}` — Paginated active fundraising causes with progress tracking.
4. `GET /api/v1/projects` & `GET /api/v1/projects/{id}` — Core service projects catalog.
5. `GET /api/v1/blogs` & `GET /api/v1/blogs/{id}` — Paginated published news and Dharma Vani articles.
6. `GET /api/v1/gallery` — Paginated photos and video items.
7. `GET /api/v1/team` — Approved volunteer directory with cascading filters (cadre, state, district, mandal, search) and pagination.
8. `GET /api/v1/certificates` — Statutory 80G, 12A, and registration documents.
9. `GET /api/v1/exams` & `GET /api/v1/exams/results` — Exam notice board cycles and candidate result lookup.
10. `GET /api/v1/wings` — Organization wings overview (Rudrasena, Kala Brundam, Grama Seva Dal, Organic Farmers).
11. `POST /api/v1/contact` — Direct contact inquiry submission with anti-spam protection.

### Existing Protected & Auth APIs:
- `POST /api/v1/auth/volunteer/login`
- `POST /api/v1/auth/member/send-otp` & `POST /api/v1/auth/member/verify-otp`
- `GET /api/v1/me`
- `GET /api/v1/volunteer/profile` & `GET /api/v1/volunteer/dashboard`
- `GET /api/v1/member/profile` & `GET /api/v1/member/card`

---

## 6. Flutter Navigation & Information Architecture

### Primary Mobile Navigation (Bottom Bar / Core Tabs):
1. **Home** — Exact parity with web home page.
2. **Causes** — Fundraising campaigns & Seva initiatives.
3. **Gallery** — Photos and Video documentation.
4. **Team** — Leadership & Cadre directory with search.
5. **Account / Portal** — Dynamic profile & card access for Members / Volunteers (or Login selector).

### Secondary Navigation (Drawer Menu):
- **About Us** (Mission, Values, Blessings)
- **Wings Subsystems** (Rudrasena Dal, Kala Brundam, Grama Seva Dal, Organic Farmers)
- **Sanatana Dharma Exams** (Notice Board, Exam Schedule, Check Results)
- **Statutory Disclosures** (80G & 12A Certificates)
- **Blogs & Updates**
- **Contact Us**
- **Help & Helpline**

---

## 7. First Implementation Batch (P0 Screens)

1. **Home Screen** (Full 13-section parity)
2. **About Screen** (Organization, Guru Garu blessings, 4 core values)
3. **Team Screen** (Approved cadre directory with search & filters)
4. **Gallery Screen** (Photo & Video viewer)
5. **Blogs / News Screen** (Article cards & detail modal/sheet)
6. **Campaigns / Causes Screen** (Donation causes & progress bars)
7. **Contact Screen** (Contact details & inquiry submission)
8. **Wings Information Screen** (Rudrasena & Dal wing profiles)
9. **Authentication & Profile Screens** (Member OTP login, Volunteer ID login, Profile & Digital Cards)

---

## 8. Content Safety, Design System & Testing Guidelines

- **HTML Content**: All text from admin descriptions is stripped of unsafe scripts and rendered with native text or safe lightweight markdown/styling.
- **Media Fallbacks**: Every network image uses caching with graceful fallback placeholders on connection errors.
- **Design Tokens**: Standardized `AppColors` (Brand Orange `#FF6600`, Dark Gray `#1A1A1A`, Slate `#4A4A4A`, Light Orange `#FFF5EE`), `AppTypography`, `AppCard`, `AppButton`, `AppLoadingState`, `AppEmptyState`, and `AppErrorState`.
- **Screen Sizes**: Responsive verification at 360px, 390px, and 430px logical widths with zero RenderFlex overflows.
