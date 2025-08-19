<?php

namespace OpenSoutheners\LaravelScim\Http\Middleware;

use Closure;

class ScimResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        return tap($next($request), function ($response) {
            $response->header('Content-Type', 'application/scim+json');
            $response->header('X-Scim-Response', 'true');
            $response->header('X-Scim-Version', '2.0');
        });
    }
}
