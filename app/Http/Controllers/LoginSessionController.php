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
     * @throws \App\Models\Domain\Exceptions\AccountNotFoundException
     * @throws \App\Models\Domain\Exceptions\ValidationException
     */
    public function create(Request $request): JsonResponse
    {
        $requestArray = $request->json()->all();

        $sessionId = $this->loginSessionScenario->create($requestArray);

        return response()->json($sessionId)->setStatusCode(201);
    }

    /**
     * ログインセッションを削除する
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        return response()->json()->setStatusCode(204);
    }
}
