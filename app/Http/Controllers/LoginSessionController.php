<?php
/**
 * LoginController
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LoginScenario;
use Illuminate\Http\JsonResponse;

/**
 * Class LoginController
 * @package App\Http\Controllers
 */
class LoginController extends Controller
{
    /**
     * LoginScenario
     * @var
     */
    private $loginScenario;

    /**
     * LoginController constructor.
     * @param LoginScenario $loginScenario
     */
    public function __construct(LoginScenario $loginScenario)
    {
        $this->loginScenario = $loginScenario;
    }

    /**
     * ログインセッションを発行する
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function create(Request $request): JsonResponse
    {
        $requestArray = $request->json()->all();

        // TODO リクエストのバリデーションを追加する

        $sessionId = $this->loginScenario->create($requestArray);

        return response()->json($sessionId)->setStatusCode(201);
    }
}
