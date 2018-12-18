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
     */
    public function index(Request $request): JsonResponse
    {
        $stocks = [
             [
              'id'                       => 1,
              'article_id'               => '1234567890abcdefghij',
              'title'                    => 'タイトル',
              'user_id'                  => 'test-user',
              'profile_image_url'        => 'http://test.com/test-image.jpag',
              'article_created_at'       => '2018-12-01 00:00:00.000000',
              'tags'                     => ['laravel5.6', 'laravel', 'php']
            ],
            [
                'id'                       => 2,
                'article_id'               => '1234567890abcdefghij',
                'title'                    => 'タイトル2',
                'user_id'                  => 'test-user2',
                'profile_image_url'        => 'http://test.com/test-image2.jpag',
                'article_created_at'       => '2018-12-01 00:00:00.000000',
                'tags'                     => ['laravel5.6', 'laravel', 'php']
            ]
        ];

        $totalCount = 9;
        $link = '<http://127.0.0.1/api/stocks?page=4&per_page=2>; rel="next"';
        $link .= '<http://127.0.0.1/api/stocks?page=5&per_page=2>; rel="last"';
        $link .= '<http://127.0.0.1/api/stocks?page=1&per_page=2>; rel="first"';
        $link .= '<http://127.0.0.1/api/stocks?page=2&per_page=2>; rel="prev"';

        return response()
            ->json($stocks)
            ->setStatusCode(200)
            ->header('Total-Count', $totalCount)
            ->header('Link', $link);
    }
}
