<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and apply strict production-grade security headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 1. Prevent MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 2. Clickjacking frame protection
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 3. Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 4. Permissions Policy
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(self "https://api.razorpay.com" "https://checkout.razorpay.com" "https://*.cashfree.com")');

        // 5. Content Security Policy (Strict yet compatible with Tailwind CDN, Google Fonts, dynamic QR, Cashfree & Razorpay)
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://sdk.cashfree.com https://checkout.razorpay.com https://api.razorpay.com https://cdn.razorpay.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: blob: https:",
            "connect-src 'self' https: wss: ws: https://api.cashfree.com https://sandbox.cashfree.com https://api.razorpay.com https://lumberjack.razorpay.com",
            "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://sdk.cashfree.com https://sandbox.cashfree.com https://api.razorpay.com https://checkout.razorpay.com",
            "object-src 'none'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
            "form-action 'self' https://sandbox.cashfree.com https://api.cashfree.com https://api.razorpay.com https://checkout.razorpay.com",
        ];
        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        // 6. Strict-Transport-Security (Only emitted on HTTPS requests to preserve local HTTP development)
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // 7. X-Robots-Tag Header Protection for Admin, Volunteer Portal & Private API routes
        if ($request->is('admin*') || $request->is('volunteer/dashboard*') || $request->is('volunteer/member-data*') || $request->is('volunteer/profile*')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
