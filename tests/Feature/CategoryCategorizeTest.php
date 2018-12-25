<?php
/**
 * CategoryCategorizeTest
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class CategoryCategorizeTest
 * @package Tests\Feature
 */
class CategoryCategorizeTest extends AbstractTestCase
{
    use RefreshDatabase;

    /**
     * 正常系のテスト
     * カテゴリとストックのリレーションが作成されること
     */
    public function testSuccessCreate()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';

        $categoryId = 1;

        $jsonResponse = $this->postJson(
            '/api/categories/stocks',
            [
                'id' => $categoryId,
                'articleIds' => ['d210ddc2cb1bfeea9331','d210ddc2cb1bfeea9332','d210ddc2cb1bfeea9333']
            ],
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $jsonResponse->assertStatus(201);
        $jsonResponse->assertHeader('X-Request-Id');
    }
}
