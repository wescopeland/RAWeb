<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Closure;
use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

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
