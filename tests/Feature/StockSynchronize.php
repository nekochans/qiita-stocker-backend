<?php
/**
 * StockSynchronize
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class StockSynchronize
 * @package Tests\Feature
 */
class StockSynchronize extends AbstractTestCase
{
    use RefreshDatabase;

    /**
     * 正常系のテスト
     * ストックの同期ができること
     */
    public function testSuccess()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';

        $jsonResponse = $this->put(
            '/api/stocks',
            [],
            ['Authorization' => 'Bearer '.$loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertHeader('X-Request-Id');
    }
}
