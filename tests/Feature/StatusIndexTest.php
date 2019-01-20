<?php
/**
 * StatusIndexTest
 */

namespace Tests\Feature;

/**
 * Class StatusIndexTest
 * @package Tests\Feature
 */
class StatusIndexTest extends AbstractTestCase
{
    /**
     * 正常系のテスト
     * カテゴリ一覧が取得できること
     */
    public function testSuccess()
    {
        $jsonResponse = $this->get(
            '/api/statuses'
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertHeader('X-Request-Id');
    }

    /**
     * 正常系のテスト
     * メンテナンス中もエラーとならないこと
     */
    public function testSuccessMaintenance()
    {
        \Config::set('app.maintenance', true);
        $jsonResponse = $this->get(
            '/api/statuses'
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertHeader('X-Request-Id');
    }
}
