<?php
/**
 * CategoryController
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\CategoryScenario;

/**
 * Class CategoryController
 * @package App\Http\Controllers
 */
class CategoryController extends Controller
{
    /**
     * CategoryScenario
     * @var
     */
    private $categoryScenario;

    /**
     * CategoryController constructor.
     * @param CategoryScenario $categoryScenario
     */
    public function __construct(CategoryScenario $categoryScenario)
    {
        $this->categoryScenario = $categoryScenario;
    }

    /**
     * カテゴリ一覧を取得する
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $categories = [

            [
                'categoryId'   => 1,
                'name'         => 'カテゴリ名1'

            ],
            [
                'categoryId'   => 2,
                'name'         => 'カテゴリ名2'
            ],
            [
                'categoryId'   => 3,
                'name'         => 'カテゴリ名3'
            ]
        ];

        return response()->json($categories)->setStatusCode(200);
    }


    /**
     * カテゴリを作成する
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \App\Models\Domain\exceptions\LoginSessionExpiredException
     * @throws \App\Models\Domain\exceptions\UnauthorizedException
     */
    public function create(Request $request): JsonResponse
    {
        $requestArray = $request->json()->all();

        $sessionId = $request->bearerToken();
        $params = [
            'sessionId' => $sessionId
        ];

        $params = array_merge($params, $requestArray);

        $category = $this->categoryScenario->create($params);

        return response()->json($category)->setStatusCode(201);
    }
}
