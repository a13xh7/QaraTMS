<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // priority: query param `lang` -> session -> env APP_LOCALE
        $locale = $request->query('lang');

        if (!$locale) {
            $locale = session('app_locale', env('APP_LOCALE', 'en'));
        }

        if ($locale) {
            App::setLocale($locale);
            session(['app_locale' => $locale]);
        }

        return $next($request);
    }
}
