<?php
/**
 * LoginSessionController
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\LoginSessionScenario;

/**
 * Class LoginSessionController
 * @package App\Http\Controllers
 */
class LoginSessionController extends Controller
{
    /**
     * LoginSessionScenario
     * @var
     */
    private $loginSessionScenario;

    /**
     * LoginSessionController constructor.
     * @param LoginSessionScenario $loginSessionScenario
     */
    public function __construct(LoginSessionScenario $loginSessionScenario)
    {
        $this->loginSessionScenario = $loginSessionScenario;
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

        $sessionId = $this->loginSessionScenario->create($requestArray);

        return response()->json($sessionId)->setStatusCode(201);
    }
}
