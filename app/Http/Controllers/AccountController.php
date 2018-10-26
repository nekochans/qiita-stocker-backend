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
     * @throws \App\Models\Domain\exceptions\AccountCreatedException
     * @throws \App\Models\Domain\exceptions\ValidationException
     */
    public function create(Request $request): JsonResponse
    {
        $requestArray = $request->json()->all();

        $sessionId = $this->accountScenario->create($requestArray);

        return response()->json($sessionId)->setStatusCode(201);
    }

    /**
     * アカウントを削除する
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \App\Models\Domain\exceptions\UnauthorizedException
     */
    public function destroy(Request $request): JsonResponse
    {
        $sessionId = $request->bearerToken();

        // TODO セッションIDが存在しなかった場合のエラー処理を追加する
        $params = [
            'sessionId' => $sessionId
        ];

        $this->accountScenario->destroy($params);
        return response()->json()->setStatusCode(204);
    }
}
