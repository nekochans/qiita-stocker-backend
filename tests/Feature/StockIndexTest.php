<?php
/**
 * StockIndexTest
 */

namespace Tests\Feature;

use App\Eloquents\Stock;
use App\Eloquents\Account;
use App\Eloquents\Category;
use App\Eloquents\StockTag;
use Faker\Factory as Faker;
use App\Eloquents\AccessToken;
use App\Eloquents\CategoryName;
use App\Eloquents\LoginSession;
use App\Eloquents\QiitaAccount;
use App\Eloquents\QiitaUserName;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class StockIndexTest
 * @package Tests\Feature
 */
class StockIndexTest extends AbstractTestCase
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
     * ストックを取得できること
     */
    public function testSuccess()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);

        $page = 2;
        $perPage = 20;
        $stockCount = 99;

        $stockList = $this->createStocks($perPage);

        $fetchStockList = [];
        foreach ($stockList as $stock) {
            $fetchStoc = $this->createFetchStocksData($stock);
            array_push($fetchStockList, $fetchStoc);
        }

        $mockData = [[200, ['total-count' => $stockCount], json_encode($fetchStockList)]];
        $this->setMockGuzzle($mockData);

        $uri = sprintf(
            '/api/stocks?page=%d&per_page=%d',
            $page,
            $perPage
        );

        $jsonResponse = $this->get(
            $uri,
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        $link = sprintf('<http://127.0.0.1/api/stocks?page=3&per_page=%d>; rel="next", ', $perPage);
        $link .= sprintf('<http://127.0.0.1/api/stocks?page=5&per_page=%d>; rel="last", ', $perPage);
        $link .= sprintf('<http://127.0.0.1/api/stocks?page=1&per_page=%d>; rel="first", ', $perPage);
        $link .= sprintf('<http://127.0.0.1/api/stocks?page=1&per_page=%d>; rel="prev"', $perPage);

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $jsonResponse->assertJson($stockList);
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertHeader('X-Request-Id');
        $jsonResponse->assertHeader('Link', $link);
        $jsonResponse->assertHeader('Total-Count', $stockCount);
    }

    /**
     * ストックのデータを作成する
     *
     * @param int $count
     * @return array
     */
    private function createStocks(int $count) :array
    {
        $stocks = [];
        for ($i = 0; $i < $count; $i++) {
            $secondTag = $i + 1;

            $stock = [
                'article_id'                => 'abcdefghij'. str_pad($i, 10, '0'),
                'title'                     => 'title' . $i,
                'user_id'                   => 'user-id-' . $i,
                'profile_image_url'         => 'http://test.com/test-image-updated.jpag'. $i,
                'article_created_at'        => '2018-01-01 00:11:22.000000',
                'tags'                      => ['tag'. $i, 'tag'. $secondTag]
            ];
            array_push($stocks, $stock);
        }

        return $stocks;
    }

    /**
     * APIから取得するストックのデータを作成する
     *
     * @param array $stock
     * @return array
     */
    private function createFetchStocksData(array $stock) :array
    {
        $faker = Faker::create();
        $tags = [];
        for ($i = 0; $i < count($stock['tags']); $i++) {
            $tag = [
                'name'     => $stock['tags'][$i],
                'versions' => []
            ];
            array_push($tags, $tag);
        }

        $fetchStock = [
            'rendered_body'   => '<h1>Example</h1>',
            'body'            => '# Example',
            'coediting'       => false,
            'comments_count'  => 0,
            'created_at'      => $stock['article_created_at'],
            'group'           => null,
            'id'              => $stock['article_id'],
            'likes_count'     => 50,
            'private'         => false,
            'reactions_count' => 0,
            'tags'            => $tags,
            'title'           => $stock['title'],
            'updated_at'      => $faker->dateTimeThisDecade,
            'url'             => 'https://qiita.com/yaotti/items/4bd431809afb1bb99e4f',
            'user'            => [
                'description'         => 'Hello, world.',
                'facebook_id'         => '',
                'followees_count'     => 100,
                'followers_count'     => 200,
                'github_login_name'   => '',
                'id'                  => $stock['user_id'],
                'items_count'         => 300,
                'linkedin_id'         => '',
                'location'            => 'Tokyo, Japan',
                'name'                => '',
                'organization'        => 'test Inc',
                'permanent_id'        => 1,
                'profile_image_url'   => $stock['profile_image_url'],
                'team_only'           => false,
                'twitter_screen_name' => '',
                'website_url'         => '',
            ],
            'page_views_count' => null
        ];
        return $fetchStock;
    }

    /**
     * 異常系のテスト
     * APIのレスポンスがエラーの場合、エラーとなること
     */
    public function testErrorApiFailure()
    {
        $errorResponse = [
            'message' => 'Not found',
            'type'    => 'not_found'
        ];

        $mockData = [[404, [], json_encode($errorResponse)]];
        $this->setMockGuzzle($mockData);

        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);

        $uri = sprintf(
            '/api/stocks?page=%d&per_page=%d',
            1,
            20
        );

        $jsonResponse = $this->get(
            $uri,
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 503;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => 'Service Unavailable']);
        $jsonResponse->assertStatus($expectedErrorCode);
        $jsonResponse->assertHeader('X-Request-Id');
    }

    /**
     * 異常系のテスト
     * Authorizationが存在しない場合エラーとなること
     */
    public function testErrorLoginSessionNull()
    {
        $uri = sprintf('/api/stocks?page=%d&per_page=%d', 1, 20);
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
        $uri = sprintf('/api/stocks?page=%d&per_page=%d', 1, 20);
        $jsonResponse = $this->get(
            $uri,
            ['Authorization' => 'Bearer '.$loginSession]
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

        $uri = sprintf('/api/stocks?page=%d&per_page=%d', 1, 20);
        $jsonResponse = $this->get(
            $uri,
            ['Authorization' => 'Bearer '.$loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 401;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => 'セッションの期限が切れました。再度、ログインしてください。']);
        $jsonResponse->assertStatus($expectedErrorCode);
        $jsonResponse->assertHeader('X-Request-Id');
    }

    /**
     * 異常系のテスト
     * ストック取得時のクエリパラメータ page のバリデーション
     *
     * @param $page
     * @dataProvider pageProvider
     */
    public function testErrorPageValidation($page)
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId]);

        $uri = sprintf(
            '/api/stocks?page=%s&per_page=%d',
            $page,
            2
        );

        $jsonResponse = $this->get(
            $uri,
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 422;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => '不正なリクエストが行われました。']);
        $jsonResponse->assertStatus($expectedErrorCode);
        $jsonResponse->assertHeader('X-Request-Id');
    }

    /**
     * page のデータプロバイダ
     *
     * @return array
     */
    public function pageProvider()
    {
        return [
            'emptyString'        => [''],
            'null'               => [null],
            'string'             => ['a'],
            'symbol'             => ['1/'],
            'multiByte'          => ['１'],
            'negativeNumber'     => [-1],
            'double'             => [1.1],
            'lessThanMin'        => [0],
            'greaterThanMax'     => [101],
        ];
    }

    /**
     * 異常系のテスト
     * ストック取得時のクエリパラメータ perPage のバリデーション
     *
     * @param $perPage
     * @dataProvider perPageProvider
     */
    public function testErrorPerPageValidation($perPage)
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId]);

        $uri = sprintf(
            '/api/stocks?page=%d&per_page=%s',
            1,
            $perPage
        );

        $jsonResponse = $this->get(
            $uri,
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 422;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => '不正なリクエストが行われました。']);
        $jsonResponse->assertStatus($expectedErrorCode);
        $jsonResponse->assertHeader('X-Request-Id');
    }

    /**
     * perPage のデータプロバイダ
     *
     * @return array
     */
    public function perPageProvider()
    {
        return [
            'emptyString'        => [''],
            'null'               => [null],
            'string'             => ['a'],
            'symbol'             => ['1;'],
            'multiByte'          => ['１'],
            'negativeNumber'     => [-1],
            'double'             => [1.1],
            'lessThanMin'        => [0],
            'greaterThanMax'     => [101],
        ];
    }
}
