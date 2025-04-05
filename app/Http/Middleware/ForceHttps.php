<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps extends Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // TODO enable HSTS on the production server and get rid of this middleware
        if (!$request->secure() && app()->environment('stage', 'production')) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
