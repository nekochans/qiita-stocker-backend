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

    public function create(Request $request): JsonResponse
    {
        $requestArray = $request->json()->all();

        $sessionId = $request->bearerToken();
        $params = [
            'sessionId' => $sessionId
        ];

        $params = array_merge($params, $requestArray);

        $categories = $this->categoryScenario->create($params);

        return response()->json($categories)->setStatusCode(201);
    }
}
