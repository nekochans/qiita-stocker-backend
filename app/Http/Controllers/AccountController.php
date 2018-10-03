<?php
/**
 * AccountController
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AccountScenario;
use Illuminate\Http\JsonResponse;

/**
 * Class AccountController
 * @package App\Http\Controllers
 */
class AccountController extends Controller
{
    /**
     * AccountScenario
     * @var
     */
    private $accountScenario;

    /**
     * AccountController constructor.
     * @param AccountScenario $accountScenario
     */
    public function __construct(AccountScenario $accountScenario)
    {
        $this->accountScenario = $accountScenario;
    }

    /**
     * アカウントを登録する
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function registration(Request $request): JsonResponse
    {
        $requestArray = $request->json()->all();

        // TODO リクエストのバリデーションを追加する

        $sessionId = $this->accountScenario->create($requestArray);

        return response()->json($sessionId)->setStatusCode(201);
    }
}
