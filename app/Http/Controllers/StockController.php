<?php
/**
 * StockController
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Class StockController
 * @package App\Http\Controllers
 */
class StockController extends Controller
{
    /**
     * ストックを同期する
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function synchronize(Request $request): JsonResponse
    {
        return response()->json()->setStatusCode(200);
    }
}
