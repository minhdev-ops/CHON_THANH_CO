<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('locale') ?? $request->header('X-Locale');

        $validLocales = ['vi', 'en'];
        $locale = in_array($locale, $validLocales, true) ? $locale : 'vi';

        app()->setLocale($locale);

        return $next($request);
    }
}