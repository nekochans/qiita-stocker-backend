<?php
/**
 * StockIndexTest
 */

namespace Tests\Feature;

;

use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class StockIndexTest
 * @package Tests\Feature
 */
class StockIndexTest extends AbstractTestCase
{
    use RefreshDatabase;
    /**
     * 正常系のテスト
     * ストックの同期ができること
     */
    public function testSuccess()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $page = 2;
        $perPage = 2;

        $uri = sprintf(
            '/api/stocks?page=%d&per_page=%d',
            $page,
            $perPage
        );

        $jsonResponse = $this->get(
            $uri,
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        $stocks = [
            [
                'id'                       => 1,
                'article_id'               => '1234567890abcdefghij',
                'title'                    => 'タイトル',
                'user_id'                  => 'test-user',
                'profile_image_url'        => 'http://test.com/test-image.jpag',
                'article_created_at'       => '2018-12-01 00:00:00.000000',
                'tags'                     => ['laravel5.6', 'laravel', 'php']
            ],
            [
                'id'                       => 2,
                'article_id'               => '1234567890abcdefghij',
                'title'                    => 'タイトル2',
                'user_id'                  => 'test-user2',
                'profile_image_url'        => 'http://test.com/test-image2.jpag',
                'article_created_at'       => '2018-12-01 00:00:00.000000',
                'tags'                     => ['laravel5.6', 'laravel', 'php']
            ]
        ];

        $totalCount = 9;
        $link = '<http://127.0.0.1/api/stocks?page=4&per_page=2>; rel="next"';
        $link .= '<http://127.0.0.1/api/stocks?page=5&per_page=2>; rel="last"';
        $link .= '<http://127.0.0.1/api/stocks?page=1&per_page=2>; rel="first"';
        $link .= '<http://127.0.0.1/api/stocks?page=2&per_page=2>; rel="prev"';

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $jsonResponse->assertJson($stocks);
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertHeader('X-Request-Id');
        $jsonResponse->assertHeader('Link', $link);
        $jsonResponse->assertHeader('Total-Count', $totalCount);
    }
}
