<?php
/**
 * CategoryDestroyCategorize
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class CategoryDestroyCategorize
 * @package Tests\Feature
 */
class CategoryDestroyCategorize extends AbstractTestCase
{
    use RefreshDatabase;

    /**
     * 正常系のテスト
     */
    public function testSuccess()
    {
        $id = 1;
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $jsonResponse = $this->delete(
            '/api/categories/stocks/'. $id,
            [],
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $jsonResponse->assertStatus(204);
        $jsonResponse->assertHeader('X-Request-Id');
    }

    /**
     * 異常系のテスト
     * メンテナンス中の場合エラーとなること
     */
    public function testErrorMaintenance()
    {
        \Config::set('app.maintenance', true);
        $id = 1;
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $jsonResponse = $this->delete(
            '/api/categories/stocks/'. $id,
            [],
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 503;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => 'サービスはメンテナンス中です。']);
        $jsonResponse->assertStatus($expectedErrorCode);
    }
}
