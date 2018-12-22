<?php
/**
 * StockController
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StockScenario;
use Illuminate\Http\JsonResponse;

/**
 * Class StockController
 * @package App\Http\Controllers
 */
class StockController extends Controller
{
    /**
     * StockScenario
     * @var
     */
    private $stockScenario;

    /**
     * StockController constructor.
     * @param StockScenario $stockScenario
     */
    public function __construct(StockScenario $stockScenario)
    {
        $this->stockScenario = $stockScenario;
    }

    /**
     * ストックを同期する
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \App\Models\Domain\Exceptions\LoginSessionExpiredException
     * @throws \App\Models\Domain\Exceptions\ServiceUnavailableException
     * @throws \App\Models\Domain\Exceptions\UnauthorizedException
     */
    public function synchronize(Request $request): JsonResponse
    {
        $sessionId = $request->bearerToken();
        $params = [
            'sessionId' => $sessionId
        ];

        $this->stockScenario->synchronize($params);
        return response()->json()->setStatusCode(200);
    }

    /**
     * ストック一覧を取得する
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \App\Models\Domain\Exceptions\LoginSessionExpiredException
     * @throws \App\Models\Domain\Exceptions\UnauthorizedException
     * @throws \App\Models\Domain\Exceptions\ValidationException
     */
    public function index(Request $request): JsonResponse
    {
        $sessionId = $request->bearerToken();
        $params = [
            'sessionId'    => $sessionId,
            'page'         => $request->query('page'),
            'perPage'      => $request->query('per_page'),
            'uri'          => env('APP_URL') . $request->getPathInfo()
        ];

        $response = $this->stockScenario->index($params);

        return response()
            ->json($response['stocks'])
            ->setStatusCode(200)
            ->header('Total-Count', $response['totalCount'])
            ->header('Link', $response['link']);
    }
}
