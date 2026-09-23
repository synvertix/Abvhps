<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Remember the visitor's language (session + one-year cookie) and go back to where they were.
     */
    public function switch(Request $request, string $locale)
    {
        abort_unless(array_key_exists($locale, config('abvhps.locales', [])), 404);

        $request->session()->put('locale', $locale);

        return redirect($this->safeReturnUrl($request))
            ->withCookie(cookie(SetLocale::COOKIE, $locale, 60 * 24 * 365));
    }

    /** Only ever return to a page on this same site (never an external Referer). */
    private function safeReturnUrl(Request $request): string
    {
        $previous = url()->previous();
        $host = parse_url($previous, PHP_URL_HOST);

        if ($host !== null && strcasecmp($host, $request->getHost()) !== 0) {
            return '/';
        }

        // Never bounce a visitor into the admin area because of a language click
        $path = (string) parse_url($previous, PHP_URL_PATH);
        if (preg_match('#/admin(/|$)#', $path) === 1 || preg_match('#/lang/#', $path) === 1) {
            return '/';
        }

        return $previous ?: '/';
    }
}
