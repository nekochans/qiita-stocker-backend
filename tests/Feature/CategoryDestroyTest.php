<?php
/**
 * CategoryDestroyTest
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class CategoryDestroyTest
 * @package Tests\Feature
 */
class CategoryDestroyTest extends AbstractTestCase
{
    use RefreshDatabase;

    /**
     * 正常系のテスト
     * カテゴリを削除できること
     */
    public function testSuccess()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $categoryId = 1;

        $jsonResponse = $this->delete(
            '/api/categories/'. $categoryId,
            [],
            ['Authorization' => 'Bearer '.$loginSession]
        );

        $jsonResponse->assertStatus(204);
        $jsonResponse->assertHeader('X-Request-Id');
    }
}
