# ABVHPS Mobile App

Official cross-platform mobile application for **Akhanda Bharata Viswa Hindu Parirakshana Samiti (ABVHPS)** built with Flutter.

| Attribute | Value |
| :--- | :--- |
| **User-Facing App Name** | `ABVHPS` |
| **Official Organization** | `Akhanda Bharata Viswa Hindu Parirakshana Samiti` |
| **Technical Project Name** | `abvhpsapp` |
| **Package / Bundle ID** | `org.abvhps.abvhpsapp` |

---

## 1. Overview & Architecture
The mobile application communicates **exclusively** with the authoritative Laravel backend over secure HTTPS RESTful endpoints (`/api/v1/`):
```text
Flutter Mobile App (mobile_app)
       ↓ (HTTPS / JSON)
Laravel /api/v1 Endpoints
       ↓ (Sanctum Tokens / Account Isolation / Cadre Policies)
MySQL Database (Server-authoritative)
```

**Absolute Invariant**: Flutter NEVER connects directly to MySQL and never stores `.env` database credentials, payment gateway secrets, or identity document data.

---

## 2. Prerequisites
- **Flutter SDK**: 3.22+ (stable) located on `D:\flutter\flutter`
- **Dart SDK**: 3.13+ (bundled with Flutter)
- **Local Backend**: Laravel 12.x running on `http://127.0.0.1:8000`
- **Android Studio / Android SDK**: for building APK and running on Android Emulator

---

## 3. Technology Stack
- **Framework**: Flutter (Material 3)
- **HTTP Client**: `dio`
- **State Management**: `flutter_riverpod`
- **Navigation & Routing**: `go_router`
- **Secure Storage**: `flutter_secure_storage`

---

## 4. API Base URL Configuration

Pass the API Base URL at compile/run time via `--dart-define`:

### Android Emulator (Local Laravel Server)
```powershell
cd C:\xampp\htdocs\abvhps\mobile_app
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1
```

### Physical Device (Local Wi-Fi Network)
```powershell
cd C:\xampp\htdocs\abvhps\mobile_app
flutter run --dart-define=API_BASE_URL=http://<YOUR_LOCAL_IP>:8000/api/v1
```

### Production API
```powershell
cd C:\xampp\htdocs\abvhps\mobile_app
flutter run --dart-define=API_BASE_URL=https://abvhps.org/api/v1
```

---

## 5. Security Policies
1. **Tokens**: Authentication tokens are stored strictly in `flutter_secure_storage` (Android Keystore / iOS Keychain). Tokens are NEVER stored in `SharedPreferences`, plaintext files, or console logs.
2. **Device Independence**: Every mobile device supplies `device_name` and receives an independent device-specific token. Logout on one device (`/auth/logout`) does not invalidate other active devices. `/auth/logout-all` revokes all device tokens.
3. **Granular Abilities & Expiration**: Sanctum tokens carry granular abilities (`account:volunteer`, `volunteer:profile`, `volunteer:dashboard`, `member:profile`, `member:card`). New volunteers requiring password change receive restricted tokens (`volunteer:change-password`). Tokens expire automatically after 90 days (`SANCTUM_EXPIRATION_MINUTES=129600`).
4. **Role Isolation**:
   - Volunteer token -> `/api/v1/volunteer/*`
   - Member token -> `/api/v1/member/*`
   - Admin access remains strictly web-only (`https://abvhps.org/admin`).
5. **Data Privacy**: Complete Aadhaar numbers, PAN, bank accounts, and payment secrets are never stored or exposed to the mobile app.


---

## 6. Testing & Quality Checks

### Run Analyzer
```powershell
cd C:\xampp\htdocs\abvhps\mobile_app
flutter analyze
```

### Run Tests
```powershell
cd C:\xampp\htdocs\abvhps\mobile_app
flutter test
```

### Build Debug APK
```powershell
cd C:\xampp\htdocs\abvhps\mobile_app
flutter build apk --debug
```

---

## 7. Documentation
- [API Contract](docs/API_CONTRACT.md): Comprehensive endpoint parameters and response contracts.
- [Development Guide](docs/DEVELOPMENT.md): Detailed local workflow and architecture guide.
