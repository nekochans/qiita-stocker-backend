<?php

namespace App\Http\Middleware;

use Closure;

class InfoLogging
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
        $response = $next($request);

        if ($request->getPathInfo() === '/api/statuses') {
            return $response;
        }

        if (!$response->exception) {
            app('log')->info(
                $request,
                [
                    'response' => [
                        'header' => $response->headers->all(),
                        'body'   => $response->original,
                    ]
                ]
            );
        }

        return $response;
    }
}
