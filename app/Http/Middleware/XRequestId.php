<?php
/**
 * XRequestId
 */

namespace App\Http\Middleware;

use Closure;

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
        // App\Infrastructure\Logger で作成している traceId をそのまま適応
        // こうすることで X-Request-Id をキーにサーバーのログを検索しやすくしている
        $XRequestId = app('log')->getTraceId();

        return $next($request)
            ->header('X-Request-Id', $XRequestId);
    }
}
