# ABVHPS Mobile App Development Guide

## Local Development Workflow

### 1. Prerequisites
- **Flutter SDK**: 3.22+ (located at `D:\flutter\flutter`)
- **PHP**: 8.2+ with SQLite and OpenSSL extensions
- **Composer**: 2.x
- **Android Studio / Android SDK**: for Android Emulator & SDK builds

### 2. Setting Up Backend (Laravel)
Start the local Laravel development server:
```powershell
cd C:\xampp\htdocs\abvhps
php artisan serve --host=0.0.0.0 --port=8000
```
Verify the server is running by visiting:
`http://127.0.0.1:8000/api/v1/health`

### 3. Setting Up Flutter Application
Navigate to the `mobile_app` directory:
```powershell
cd C:\xampp\htdocs\abvhps\mobile_app
flutter pub get
```

### 4. Running the Flutter App

#### Android Emulator
Android emulator maps host `127.0.0.1` to `10.0.2.2`:
```powershell
cd C:\xampp\htdocs\abvhps\mobile_app
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api/v1
```

#### Physical Android / iOS Device on Same Wi-Fi
Find your workstation's local IP address (e.g. `192.168.1.10`):
```powershell
cd C:\xampp\htdocs\abvhps\mobile_app
flutter run --dart-define=API_BASE_URL=http://192.168.1.10:8000/api/v1
```

### 5. Running Tests

#### Backend API Tests
```powershell
cd C:\xampp\htdocs\abvhps
php artisan test --filter=MobileApi
```

#### Flutter Unit & Widget Tests
```powershell
cd C:\xampp\htdocs\abvhps\mobile_app
flutter test
```

### 6. Security Guidelines for Mobile Developers
- Never store API tokens in `SharedPreferences` or plaintext storage; always use `flutter_secure_storage`.
- Provide a clear, non-sensitive `device_name` label (e.g. "ABVHPS Mobile App" or model string) during login/OTP verification. Never send hardware serials or IMEI.
- Multi-device sessions are independent: logging out via `/auth/logout` revokes only the current device token, while `/auth/logout-all` terminates all sessions.
- Enforce restricted password change flow whenever `must_change_password` is true in auth state.
- Never hardcode API keys, passwords, or test credentials in the Dart code.
- Never bypass backend validation or error responses.

