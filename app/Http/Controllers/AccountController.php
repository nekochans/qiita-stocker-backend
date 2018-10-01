<?php
/**
 * AccountController
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\RegistrationScenario;

/**
 * Class AccountController
 * @package App\Http\Controllers
 */
class AccountController extends Controller
{
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

        $registrationScenario = new RegistrationScenario();
        $sessionId = $registrationScenario->registration($requestArray);

        return response()->json($sessionId)->setStatusCode(201);
    }
}
