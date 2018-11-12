<?php
/**
 * CategoryController
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Class CategoryController
 * @package App\Http\Controllers
 */
class CategoryController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        $requestArray = $request->json()->all();
        // TODO シナリオクラスを作成しカテゴリを登録する

        $categories = [
            'categoryId'   => 1,
            'name' => '設計'
        ];

        return response()->json($categories)->setStatusCode(201);
    }
}
