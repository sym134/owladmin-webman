<?php

namespace plugin\jzadmin\middleware;

use Closure;
use Illuminate\Support\Facades\App;

class AutoSetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = request()->header('locale', config('translation.locale')); // webman config('translation.locale')
        App::setLocale($locale);
        return $next($request);
    }
}
