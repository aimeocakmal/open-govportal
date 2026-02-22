<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class AllowPreview
{
    /**
     * Handle an incoming request.
     *
     * Check for a valid signed URL and share the preview flag with all views.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isPreview = $request->has('signature') && URL::hasValidSignature($request);

        View::share('isPreview', $isPreview);

        return $next($request);
    }
}
