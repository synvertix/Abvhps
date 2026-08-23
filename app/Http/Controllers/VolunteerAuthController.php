<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Volunteer;
use App\Models\NotificationLog;

class VolunteerAuthController extends Controller
{
    /**
     * Display the dedicated Volunteer Login Form.
     */
    public function showLoginForm()
    {
        if (Auth::guard('volunteer')->check()) {
            $volunteer = Auth::guard('volunteer')->user();
            if ($volunteer->must_change_password) {
                return redirect()->route('volunteer.change_password');
            }
            return redirect()->route('volunteer.dashboard');
        }

        return view('auth.volunteer_login');
    }

    /**
     * Authenticate the approved volunteer using 6-digit Login ID and password.
     */
    public function login(Request $request)
    {
        $request->validate([
            'volunteer_id' => 'required|string',
            'password' => 'required|string',
        ], [
            'volunteer_id.required' => 'Please enter your 6-digit Volunteer ID.',
            'password.required' => 'Please enter your password.',
        ]);

        $loginInput = trim($request->input('volunteer_id'));
        $password = $request->input('password');

        $throttleKey = 'volunteer_login:' . $loginInput . '|' . $request->ip();

        // Check Rate Limiter (Max 5 attempts per 60 seconds)
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);

            \App\Services\AuditLogger::log('VOLUNTEER_LOGIN_THROTTLED', 'Volunteer', $loginInput, [
                'cooldown_seconds' => $seconds
            ], 'Anonymous', $loginInput);

            return back()->withInput($request->only('volunteer_id'))
                ->withErrors(['volunteer_id' => "Too many login attempts. Please try again in {$seconds} seconds."]);
        }

        // Lookup volunteer by 6-digit volunteer_login_id OR volunteer_id
        $volunteer = Volunteer::where('volunteer_login_id', $loginInput)
            ->orWhere('volunteer_id', $loginInput)
            ->first();

        if (!$volunteer) {
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);

            \App\Services\AuditLogger::log('VOLUNTEER_LOGIN_FAILED', 'Volunteer', $loginInput, [
                'reason' => 'ID_NOT_FOUND'
            ], 'Anonymous', $loginInput);

            return back()->withInput($request->only('volunteer_id'))
                ->withErrors(['volunteer_id' => 'Invalid Volunteer ID or credentials.']);
        }

        // Strict verification gate: only APPROVED & ACTIVE volunteers can log in
        if ($volunteer->status !== 'approved' || (isset($volunteer->is_active) && !$volunteer->is_active)) {
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);

            $reason = match ($volunteer->status) {
                'pending' => 'Your volunteer application is currently pending review and has not yet been approved.',
                'rejected' => 'Your volunteer application was not approved. Please contact administration.',
                default => 'Your volunteer account is currently inactive. Please contact administration.',
            };

            \App\Services\AuditLogger::log('VOLUNTEER_LOGIN_BLOCKED', 'Volunteer', $volunteer->volunteer_id, [
                'status' => $volunteer->status,
                'is_active' => $volunteer->is_active ?? false
            ], 'Volunteer', $volunteer->volunteer_id, $volunteer->id);

            return back()->withInput($request->only('volunteer_id'))
                ->withErrors(['volunteer_id' => $reason]);
        }

        // Attempt authentication using volunteer guard
        if (Hash::check($password, $volunteer->password)) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);

            Auth::guard('volunteer')->login($volunteer, $request->filled('remember'));
            $request->session()->regenerate();

            \App\Services\AuditLogger::log('VOLUNTEER_LOGIN_SUCCESS', 'Volunteer', $volunteer->volunteer_id, [
                'name' => $volunteer->full_name
            ], 'Volunteer', $volunteer->volunteer_id, $volunteer->id);

            // Check if first-login password change is mandatory
            if ($volunteer->must_change_password) {
                return redirect()->route('volunteer.change_password')
                    ->with('warning', 'For security, you must change your default temporary password before proceeding.');
            }

            return redirect()->route('volunteer.dashboard')
                ->with('success', 'Welcome back, ' . $volunteer->full_name . '!');
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);

        \App\Services\AuditLogger::log('VOLUNTEER_LOGIN_FAILED', 'Volunteer', $volunteer->volunteer_id, [
            'reason' => 'PASSWORD_MISMATCH'
        ], 'Anonymous', $loginInput);

        return back()->withInput($request->only('volunteer_id'))
            ->withErrors(['password' => 'The provided password is incorrect.']);
    }

    /**
     * Show First-Login Mandatory Password Change Form.
     */
    public function showChangePasswordForm()
    {
        $volunteer = Auth::guard('volunteer')->user();
        return view('auth.volunteer_change_password', compact('volunteer'));
    }

    /**
     * Process Mandatory or Self-Initiated Password Change.
     */
    public function changePassword(Request $request)
    {
        $volunteer = Auth::guard('volunteer')->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'current_password.required' => 'Please enter your current temporary password.',
            'new_password.required' => 'Please enter a new password.',
            'new_password.min' => 'Your new password must be at least 8 characters long.',
            'new_password.confirmed' => 'New password confirmation does not match.',
            'new_password.different' => 'Your new password cannot be the same as your temporary password.',
        ]);

        if (!Hash::check($request->current_password, $volunteer->password)) {
            return back()->withErrors(['current_password' => 'Your current temporary password is incorrect.']);
        }

        // Update password hash and clear mandatory password change flag
        $volunteer->password = Hash::make($request->new_password);
        $volunteer->must_change_password = false;
        $volunteer->save();

        \App\Services\AuditLogger::log('VOLUNTEER_PASSWORD_CHANGED', 'Volunteer', $volunteer->volunteer_id, [
            'changed_by' => 'Self'
        ], 'Volunteer', $volunteer->volunteer_id, $volunteer->id);

        return redirect()->route('volunteer.dashboard')
            ->with('success', 'Your password has been changed successfully. Welcome to your Volunteer Dashboard!');
    }

    /**
     * Display the Volunteer Portal Dashboard.
     */
    public function dashboard()
    {
        $volunteer = Auth::guard('volunteer')->user();
        $member = $volunteer->membership;
        $isPresident = \App\Services\VolunteerCadreScopeService::isVerifiedCadre($volunteer) && $volunteer->cadre_level !== 'volunteer';
        $subordinateUnits = $isPresident ? \App\Services\VolunteerCadreScopeService::subordinateUnitsFor($volunteer) : collect();

        return view('volunteer.dashboard', compact('volunteer', 'member', 'isPresident', 'subordinateUnits'));
    }

    /**
     * Display Volunteer Profile Details.
     */
    public function profile()
    {
        $volunteer = Auth::guard('volunteer')->user();
        $member = $volunteer->membership;

        return view('volunteer.profile', compact('volunteer', 'member'));
    }

    /**
     * Log out the authenticated volunteer.
     */
    public function logout(Request $request)
    {
        if (Auth::guard('volunteer')->check()) {
            $vol = Auth::guard('volunteer')->user();
            \App\Services\AuditLogger::log('VOLUNTEER_LOGOUT', 'Volunteer', $vol->volunteer_id, [], 'Volunteer', $vol->volunteer_id, $vol->id);
        }

        Auth::guard('volunteer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('volunteer.login')
            ->with('success', 'You have been safely logged out.');
    }

    /**
     * Show Forgot Password Request Form.
     */
    public function showForgotPasswordForm()
    {
        return view('auth.volunteer_forgot_password');
    }

    /**
     * Send Password Reset Link.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'login_identifier' => 'required|string',
        ], [
            'login_identifier.required' => 'Please provide your 6-digit Volunteer ID or registered email address.',
        ]);

        $input = trim($request->input('login_identifier'));

        $volunteer = Volunteer::where('volunteer_login_id', $input)
            ->orWhere('volunteer_id', $input)
            ->orWhere('email', $input)
            ->first();

        if (!$volunteer || $volunteer->status !== 'approved') {
            // Generic response to avoid account enumeration
            return back()->with('status', 'If an approved account matches that information, a password reset link has been dispatched.');
        }

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $volunteer->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resetUrl = url('/volunteer/reset-password/' . $token . '?email=' . urlencode($volunteer->email));

        // Log/Send notification
        $mailStatus = config('mail.default') === 'log' ? 'logged' : 'sent';
        NotificationLog::create([
            'event_type' => 'volunteer_password_reset',
            'notifiable_type' => Volunteer::class,
            'notifiable_id' => $volunteer->id,
            'channel' => 'email',
            'recipient' => $volunteer->email,
            'status' => $mailStatus,
            'metadata' => [
                'reset_url' => $resetUrl,
                'volunteer_id' => $volunteer->volunteer_login_id,
            ],
            'sent_at' => now(),
        ]);

        return back()->with('status', 'If an approved account matches that information, a password reset link has been dispatched.');
    }

    /**
     * Show Reset Password Form.
     */
    public function showResetPasswordForm(string $token, Request $request)
    {
        $email = $request->query('email');
        return view('auth.volunteer_reset_password', compact('token', 'email'));
    }

    /**
     * Process Password Reset.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'This password reset token is invalid or expired.']);
        }

        $volunteer = Volunteer::where('email', $request->email)->first();
        if (!$volunteer) {
            return back()->withErrors(['email' => 'No volunteer account found with this email.']);
        }

        $volunteer->password = Hash::make($request->password);
        $volunteer->must_change_password = false;
        $volunteer->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('volunteer.login')
            ->with('success', 'Your password has been reset successfully. Please log in with your new password.');
    }
}
