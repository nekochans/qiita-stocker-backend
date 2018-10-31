<?php
/**
 * XRequestId
 */

namespace App\Http\Middleware;

use Closure;
use Ramsey\Uuid\Uuid;

class XRequestId
{
    /**
     * Handle an incoming request.
     *
     * @param $request
     * @param Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {
        $XRequestId = Uuid::uuid4();

        return $next($request)
            ->header('X-Request-Id', $XRequestId);
    }
}
