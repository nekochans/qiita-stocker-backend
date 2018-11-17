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
     * @throws \App\Models\Domain\exceptions\LoginSessionExpiredException
     * @throws \App\Models\Domain\exceptions\UnauthorizedException
     */
    public function index(Request $request): JsonResponse
    {
        $sessionId = $request->bearerToken();
        $params = [
            'sessionId' => $sessionId
        ];

        $categories = $this->categoryScenario->index($params);

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

    /**
     * 指定されたカテゴリを更新する
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \App\Models\Domain\exceptions\LoginSessionExpiredException
     * @throws \App\Models\Domain\exceptions\UnauthorizedException
     */
    public function update(Request $request): JsonResponse
    {
        $requestArray = $request->json()->all();

        $sessionId = $request->bearerToken();
        $params = [
            'sessionId'    => $sessionId,
            'id'           => $request->id,
            'name'         => $requestArray['name']
        ];

        $category = $this->categoryScenario->update($params);

        return response()->json($category)->setStatusCode(200);
    }
}
