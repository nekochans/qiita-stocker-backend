<?php
/**
 * CheckMaintenance
 */

namespace App\Http\Middleware;

use Closure;
use App\Models\Domain\Exceptions\MaintenanceException;

class CheckMaintenance
{
    /**
     * メンテナンスモードの場合、MaintenanceExceptionをThrowする
     *
     * @param $request
     * @param Closure $next
     * @return mixed
     * @throws MaintenanceException
     */
    public function handle($request, Closure $next)
    {
        if ($request->path() !== 'api/statuses' && config('app.maintenance') === true) {
            throw new MaintenanceException('サービスはメンテナンス中です。');
        }
        return $next($request);
    }
}
