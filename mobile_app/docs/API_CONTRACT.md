# ABVHPS Mobile API V1 Contract

Base URL: `{API_BASE_URL}` (e.g. `http://10.0.2.2:8000/api/v1` or `https://abvhps.org/api/v1`)

Headers for all requests:
```http
Accept: application/json
Content-Type: application/json
```
For protected endpoints:
```http
Authorization: Bearer <SANCTUM_TOKEN>
```

---

## Token Architecture & Security Specifications

### Token Abilities Matrix
| Account / State | Token Abilities | Route Access Allowed |
| :--- | :--- | :--- |
| **Member (Normal)** | `mobile`, `account:member`, `member:profile`, `member:card` | `/me`, `/member/profile`, `/member/card`, `/auth/logout`, `/auth/logout-all` |
| **Volunteer (Normal)** | `mobile`, `account:volunteer`, `volunteer:profile`, `volunteer:dashboard` | `/me`, `/volunteer/profile`, `/volunteer/dashboard`, `/volunteer/change-password`, `/auth/logout`, `/auth/logout-all` |
| **Volunteer (Restricted - Must Change Password)** | `mobile`, `account:volunteer`, `volunteer:change-password` | `/me`, `/volunteer/change-password`, `/auth/logout`, `/auth/logout-all` (*Dashboard and Profile blocked with 403*) |

### Multi-Device Tokens & Expiration
1. **Device Identification**: Both `POST /auth/volunteer/login` and `POST /auth/member/verify-otp` require `device_name` (`required|string|max:100`). The Sanctum token name is assigned to this value.
2. **Multi-Device Support**: Each mobile device receives an independent personal access token. Logging out on Device A (`POST /auth/logout`) deletes only Device A's token. Device B remains authenticated.
3. **Logout-All**: `POST /auth/logout-all` revokes all personal access tokens associated with the authenticated account across all devices.
4. **Token Expiration**: Configured via `SANCTUM_EXPIRATION_MINUTES` in Laravel (Default: `129600` minutes = 90 days). After 90 days, the token automatically expires and returns `401 Unauthenticated`.

---

## 1. Public Endpoints

### 1.1 Health Check
- **Endpoint**: `GET /health`
- **Auth**: None
- **Response**:
```json
{
  "success": true,
  "status": "ok"
}
```

### 1.2 Public Bootstrap
- **Endpoint**: `GET /bootstrap`
- **Auth**: None
- **Response**:
```json
{
  "success": true,
  "data": {
    "organization": {
      "name": "Akhanda Bharata Viswa Hindu Parirakshana Samiti",
      "short_name": "ABVHPS",
      "tagline": null,
      "contact_email": "info@abvhps.org",
      "contact_phone": "+91 9989980055",
      "whatsapp": "+91 9989980055"
    },
    "social_links": [
      {
        "id": "facebook",
        "name": "Facebook",
        "short_name": "Facebook",
        "url": "https://facebook.com/...",
        "aria_label": "ABVHPS on Facebook"
      }
    ],
    "app_config": {
      "min_supported_version": "1.0.0",
      "latest_version": "1.0.0",
      "features": {
        "member_login": true,
        "volunteer_login": true
      }
    }
  },
  "message": null
}
```

---

## 2. Authentication Endpoints

### 2.1 Volunteer Login
- **Endpoint**: `POST /auth/volunteer/login`
- **Auth**: None
- **Request Body**:
```json
{
  "login_id": "100001",
  "password": "TemporaryOrUserPassword",
  "device_name": "Pixel 8 Pro"
}
```
- **Response (Success - Regular Access)**:
```json
{
  "success": true,
  "data": {
    "account_type": "volunteer",
    "token": "1|abc1234...",
    "must_change_password": false,
    "profile": {
      "id": 1,
      "volunteer_id": "100001",
      "volunteer_login_id": "100001",
      "full_name": "Sri Rama",
      "phone": "9876543210",
      "email": "rama@example.com",
      "photo_url": null,
      "status": "approved",
      "is_active": true,
      "cadre": "Volunteer",
      "cadre_level": "volunteer",
      "cadre_label": "Volunteer",
      "jurisdiction_summary": "General Volunteer",
      "state": "Andhra Pradesh",
      "district": "Guntur",
      "geo_mapping_status": "verified",
      "must_change_password": false
    }
  },
  "message": "Authenticated successfully."
}
```
- **Response (Must Change Password)**:
```json
{
  "success": true,
  "data": {
    "account_type": "volunteer",
    "token": "1|restricted_token...",
    "must_change_password": true,
    "profile": { ... }
  },
  "message": "Authenticated successfully."
}
```

### 2.2 Volunteer Change Password
- **Endpoint**: `POST /volunteer/change-password`
- **Auth**: `auth:sanctum` (Volunteer token or restricted must-change-password token)
- **Request Body**:
```json
{
  "current_password": "TemporaryPassword",
  "new_password": "NewSecurePassword123!",
  "new_password_confirmation": "NewSecurePassword123!",
  "device_name": "Pixel 8 Pro"
}
```
- **Response**:
```json
{
  "success": true,
  "data": {
    "account_type": "volunteer",
    "token": "2|new_full_token...",
    "must_change_password": false,
    "profile": { ... }
  },
  "message": "Your password has been changed successfully. Full dashboard access is now enabled."
}
```

### 2.3 Member Send OTP
- **Endpoint**: `POST /auth/member/send-otp`
- **Auth**: None
- **Request Body**:
```json
{
  "phone": "9876543210"
}
```
- **Response**:
```json
{
  "success": true,
  "challenge_id": "3fa85f64-5717-4562-b3fc-2c963f66afa6",
  "message": "If this mobile number is eligible, an OTP verification code has been dispatched."
}
```

### 2.4 Member Verify OTP
- **Endpoint**: `POST /auth/member/verify-otp`
- **Auth**: None
- **Request Body**:
```json
{
  "phone": "9876543210",
  "challenge_id": "3fa85f64-5717-4562-b3fc-2c963f66afa6",
  "otp": "123456",
  "device_name": "Galaxy S24"
}
```
- **Response**:
```json
{
  "success": true,
  "data": {
    "account_type": "member",
    "token": "3|member_token...",
    "profile": {
      "id": 1,
      "membership_id": "123456789012",
      "phone": "9876543210",
      "full_name": "Anjaneya",
      "is_completed": true,
      "is_identity_verified": true,
      "identity_badge": "✓ Aadhaar Verified",
      "identity_document_masked": "Aadhaar ending in 3210",
      "state": "Andhra Pradesh",
      "district": "Guntur"
    }
  },
  "message": "Member verified and authenticated successfully."
}
```

---

## 3. Account & Session Endpoints

### 3.1 Role-Aware `/me`
- **Endpoint**: `GET /me`
- **Auth**: `auth:sanctum`
- **Response (Volunteer)**:
```json
{
  "success": true,
  "data": {
    "account_type": "volunteer",
    "must_change_password": false,
    "profile": { ... },
    "capabilities": {
      "can_view_profile": true,
      "can_change_password": true,
      "can_view_events": true,
      "can_manage_hierarchy": false,
      "cadre_level": "volunteer",
      "is_president": false,
      "must_change_password": false
    }
  },
  "message": null
}
```

### 3.2 Logout (Current Device)
- **Endpoint**: `POST /auth/logout`
- **Auth**: `auth:sanctum`
- **Response**:
```json
{
  "success": true,
  "message": "Logged out successfully from this device."
}
```

### 3.3 Logout All (All Devices)
- **Endpoint**: `POST /auth/logout-all`
- **Auth**: `auth:sanctum`
- **Response**:
```json
{
  "success": true,
  "message": "Logged out successfully from all devices."
}
```

---

## 4. Protected Volunteer Endpoints

### 4.1 Volunteer Profile
- **Endpoint**: `GET /volunteer/profile`
- **Auth**: `auth:sanctum` (Volunteer token, `api.account_type:volunteer`, `api.volunteer.eligible`, `api.volunteer.password`)

### 4.2 Volunteer Dashboard & Jurisdiction
- **Endpoint**: `GET /volunteer/dashboard`
- **Auth**: `auth:sanctum` (Volunteer token)
- **Response**: Returns volunteer statistics (events conducted, total events, beneficiaries count) and subordinate jurisdictional units directory if the volunteer is a verified President.

---

## 5. Protected Member Endpoints

### 5.1 Member Profile
- **Endpoint**: `GET /member/profile`
- **Auth**: `auth:sanctum` (Member token, `api.account_type:member`)

### 5.2 Member Digital ID Card
- **Endpoint**: `GET /member/card`
- **Auth**: `auth:sanctum` (Member token)
- **Response**: JSON payload containing all verified member details, state/district, issued date, and masked document label for rendering digital membership ID cards in the app.
