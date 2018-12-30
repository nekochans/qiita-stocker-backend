<?php
/**
 * StockShowCategorizedTest
 */

namespace Tests\Feature;

use App\Eloquents\Stock;
use App\Eloquents\Account;
use App\Eloquents\Category;
use App\Eloquents\StockTag;
use App\Eloquents\AccessToken;
use App\Eloquents\CategoryName;
use App\Eloquents\LoginSession;
use App\Eloquents\QiitaAccount;
use App\Eloquents\QiitaUserName;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class StockShowCategorizedTest
 * @package Tests\Feature
 */
class StockShowCategorizedTest extends AbstractTestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();
        $accounts = factory(Account::class)->create();
        $accounts->each(function ($account) {
            factory(QiitaAccount::class)->create(['account_id' => $account->id]);
            factory(QiitaUserName::class)->create(['account_id' => $account->id]);
            factory(AccessToken::class)->create(['account_id' => $account->id]);
            factory(LoginSession::class)->create(['account_id' => $account->id]);
            $categories = factory(Category::class)->create(['account_id' => $account->id]);
            $categories->each(function ($category) {
                factory(CategoryName::class)->create(['category_id' => $category->id]);
            });
            $stocks = factory(Stock::class)->create(['account_id' => $account->id]);
            $stocks->each(function ($stock) {
                factory(StockTag::class)->create(['stock_id' => $stock->id]);
            });
        });
    }

    /**
     * 正常系のテスト
     * カテゴライズされたストック一覧ができること
     */
    public function testSuccess()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);

        $categoryId = 1;
        $page = 2;
        $perPage = 2;

        $uri = sprintf(
            '/api/stocks/categories/%d?page=%d&per_page=%d',
            $categoryId,
            $page,
            $perPage
        );

        $jsonResponse = $this->get(
            $uri,
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        $stocks = [
            [
                'id'                       => '1',
                'article_id'               => '1234567890abcdefghij',
                'title'                    => 'タイトル',
                'user_id'                  => 'test-user',
                'profile_image_url'        => 'http://test.com/test-image.jpag',
                'article_created_at'       => '2018-12-01 00:00:00.000000',
                'tags'                     => ['laravel5.6', 'laravel', 'php']
            ],
            [
                'id'                       => '2',
                'article_id'               => '1234567890abcdefghij',
                'title'                    => 'タイトル2',
                'user_id'                  => 'test-user2',
                'profile_image_url'        => 'http://test.com/test-image2.jpag',
                'article_created_at'       => '2018-12-01 00:00:00.000000',
                'tags'                     => ['laravel5.6', 'laravel', 'php']
            ]
        ];

        $totalCount = 9;
        $link = '<http://127.0.0.1/api/stocks/categories/1?page=4&per_page=2>; rel="next", ';
        $link .= '<http://127.0.0.1/api/stocks/categories/1?page=5&per_page=2>; rel="last", ';
        $link .= '<http://127.0.0.1/api/stocks/categories/1?page=1&per_page=2>; rel="first", ';
        $link .= '<http://127.0.0.1/api/stocks/categories/1?page=2&per_page=2>; rel="prev"';

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $jsonResponse->assertJson($stocks);
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertHeader('X-Request-Id');
        $jsonResponse->assertHeader('Link', $link);
        $jsonResponse->assertHeader('Total-Count', $totalCount);
    }

    /**
     * 異常系のテスト
     * Authorizationが存在しない場合エラーとなること
     */
    public function testErrorLoginSessionNull()
    {
        $uri = sprintf(
            '/api/stocks/categories/%d?page=%d&per_page=%d',
            1,
            2,
            20
        );
        $jsonResponse = $this->get($uri);

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 401;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => 'セッションが不正です。再度、ログインしてください。']);
        $jsonResponse->assertStatus($expectedErrorCode);
        $jsonResponse->assertHeader('X-Request-Id');
    }

    /**
     * 異常系のテスト
     * ログインセッションが不正の場合エラーとなること
     */
    public function testErrorLoginSessionNotFound()
    {
        $loginSession = 'notFound-2bae-4028-b53d-0f128479e650';
        $uri = sprintf(
            '/api/stocks/categories/%d?page=%d&per_page=%d',
            1,
            2,
            20
        );

        $jsonResponse = $this->get(
            $uri,
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 401;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => 'セッションが不正です。再度、ログインしてください。']);
        $jsonResponse->assertStatus($expectedErrorCode);
        $jsonResponse->assertHeader('X-Request-Id');
    }

    /**
     * 異常系のテスト
     * ログインセッションが有効期限切れの場合エラーとなること
     */
    public function testErrorLoginSessionIsExpired()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        factory(LoginSession::class)->create([
            'id'         => $loginSession,
            'account_id' => 1,
            'expired_on' => '2018-10-01 00:00:00'
        ]);

        $uri = sprintf(
            '/api/stocks/categories/%d?page=%d&per_page=%d',
            1,
            2,
            20
        );

        $jsonResponse = $this->get(
            $uri,
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 401;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => 'セッションの期限が切れました。再度、ログインしてください。']);
        $jsonResponse->assertStatus($expectedErrorCode);
        $jsonResponse->assertHeader('X-Request-Id');
    }
}
