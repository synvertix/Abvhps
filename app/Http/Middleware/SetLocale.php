<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the visitor's chosen public-site language (session, then cookie). English is the default.
 * Admin screens always stay in English so back-office labels never change with a visitor preference.
 */
class SetLocale
{
    public const COOKIE = 'abvhps_lang';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'en';

        if (!$request->is('admin', 'admin/*')) {
            $supported = array_keys(config('abvhps.locales', []));
            $candidate = $request->hasSession() ? $request->session()->get('locale') : null;
            $candidate = $candidate ?: $request->cookie(self::COOKIE);

            if (is_string($candidate) && in_array($candidate, $supported, true)) {
                $locale = $candidate;
            }
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
