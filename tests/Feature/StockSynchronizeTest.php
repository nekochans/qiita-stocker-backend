<?php
/**
 * StockSynchronizeTest
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
 * Class StockSynchronizeTest
 * @package Tests\Feature
 */
class StockSynchronizeTest extends AbstractTestCase
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
     * ストックの同期ができること
     */
    public function testSuccess()
    {
        $updateStock = [
            'article_id'               => '1234567890abcdefghij',
            'title'                    => 'ストック同期テスト🐱',
            'user_id'                  => 'test-user-updated',
            'profile_image_url'        => 'http://test.com/test-image-updated.jpag',
            'article_created_at'       => '2018-12-01 00:00:00.000000'
        ];

        $firstPageStocks = $this->createStocksData(100);
        $nextPageStocks = $this->createStocksData(1);
        $nextPageUpdateStock = $this->createUpdateStockData($updateStock);

        array_push($nextPageStocks, $nextPageUpdateStock);

        $totalStocks = array_merge($firstPageStocks, $nextPageStocks);

        $this->exportMockDataFile($firstPageStocks, 'StockSynchronizeTestMockFirst.json');
        $this->exportMockDataFile($nextPageStocks, 'StockSynchronizeTestMockNext.json');

        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);


        factory(Stock::class)->create([
            'account_id'               => $accountId,
            'article_id'               => $updateStock['article_id'],
            'title'                    => $updateStock['title'],
            'user_id'                  => $updateStock['user_id'],
            'profile_image_url'        => $updateStock['profile_image_url'],
            'article_created_at'       => $updateStock['article_created_at']
        ]);

        factory(StockTag::class)->create(['stock_id' => 2, 'name' => 'delete.tag']);

        $jsonResponse = $this->put(
            '/api/stocks',
            [],
            ['Authorization' => 'Bearer '.$loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertHeader('X-Request-Id');

        // DBのテーブルに期待した形でデータが入っているか確認する
        // ストックが削除されていることを確認
        $this->assertDatabaseMissing('stocks', [
            'id'                       => 1,
            'account_id'               => $accountId,
        ]);

        $this->assertDatabaseMissing('stocks_tags', [
            'id'                       => 1,
        ]);

        // ストックが更新されていることを確認
        $this->assertDatabaseHas('stocks', [
            'id'                       => 2,
            'account_id'               => $accountId,
            'article_id'               => $updateStock['article_id'],
            'title'                    => $updateStock['title'],
            'user_id'                  => $updateStock['user_id'],
            'profile_image_url'        => $updateStock['profile_image_url'],
            'article_created_at'       => $updateStock['article_created_at']
        ]);

        // タグが削除されていることを確認
        $this->assertDatabaseMissing('stocks_tags', [
            'stock_id'                   => 2,
            'name'                       => 'delete.tag'
        ]);

        // タグが追加されていることを確認
        $this->assertDatabaseHas('stocks_tags', [
            'stock_id'                   => 2,
            'name'                       => 'insert.tag'
        ]);

        $stockIdSequence = 3;
        $stockTagIdSequence = 4;

        // ストックが追加されていることを確認
        for ($i = 0; $i < count($totalStocks) - 1; $i++) {
            $this->assertDatabaseHas('stocks', [
                'id'                       => $stockIdSequence,
                'account_id'               => $accountId,
                'article_id'               => $totalStocks[$i]['id'],
                'title'                    => $totalStocks[$i]['title'],
                'user_id'                  => $totalStocks[$i]['user']['id'],
                'profile_image_url'        => $totalStocks[$i]['user']['profile_image_url'],
                'article_created_at'       => $totalStocks[$i]['created_at']
            ]);

            for ($j = 0; $j < count($totalStocks[$i]['tags']); $j++) {
                $this->assertDatabaseHas('stocks_tags', [
                    'id'                       => $stockTagIdSequence,
                    'stock_id'                 => $stockIdSequence,
                    'name'                     => $totalStocks[$i]['tags'][$j]['name'],
                ]);
                $stockTagIdSequence += 1;
            }

            $stockIdSequence += 1;
        }
    }

    /**
     * Mockデータをファイルに出力する
     *
     * @param array $stocks
     * @param string $fileName
     */
    private function exportMockDataFile(array $stocks, string $fileName)
    {
        file_put_contents(dirname(__FILE__) . '/' . $fileName, json_encode($stocks));
    }

    /**
     * ストックのデータを作成する
     *
     * @param int $count
     * @return array
     */
    private function createStocksData(int $count) :array
    {
        $faker = Faker::create();

        $stocks = [];
        for ($i = 0; $i < $count; $i++) {
            $stock = [
                'rendered_body'   => '<h1>Example</h1>',
                'body'            => '# Example',
                'coediting'       => false,
                'comments_count'  => 0,
                'created_at'      => '2018-12-12T09:00:43+09:00',
                'group'           => null,
                'id'              => $faker->unique()->regexify('[a-z0-9]{20}'),
                'likes_count'     => 50,
                'private'         => false,
                'reactions_count' => 0,
                'tags'            => [
                        0 => [
                                'name'     => $faker->word,
                                'versions' => []
                            ],
                        1 => [
                            'name'     => $faker->word,
                            'versions' => [],
                            ],
                    ],
                'title'      => $faker->sentence,
                'updated_at' => $faker->dateTimeThisDecade,
                'url'        => 'https://qiita.com/yaotti/items/4bd431809afb1bb99e4f',
                'user'       => [
                        'description'         => 'Hello, world.',
                        'facebook_id'         => '',
                        'followees_count'     => 100,
                        'followers_count'     => 200,
                        'github_login_name'   => '',
                        'id'                  => $faker->userName,
                        'items_count'         => 300,
                        'linkedin_id'         => '',
                        'location'            => 'Tokyo, Japan',
                        'name'                => '',
                        'organization'        => 'test Inc',
                        'permanent_id'        => 1,
                        'profile_image_url'   => $faker->url,
                        'team_only'           => false,
                        'twitter_screen_name' => '',
                        'website_url'         => '',
                    ],
                'page_views_count' => null
            ];

            array_push($stocks, $stock);
        }

        return $stocks;
    }

    /**
     * ストックの更新用データを作成する
     *
     * @param array $updateStock
     * @return array
     */
    private function createUpdateStockData(array $updateStock) :array
    {
        $faker = Faker::create();

        $stock = [
                'rendered_body'   => '<h1>Example</h1>',
                'body'            => '# Example',
                'coediting'       => false,
                'comments_count'  => 0,
                'created_at'      => $updateStock['article_created_at'],
                'group'           => null,
                'id'              => $updateStock['article_id'],
                'likes_count'     => 50,
                'private'         => false,
                'reactions_count' => 0,
                'tags'            => [
                    0 => [
                        'name'     => 'insert.tag',
                        'versions' => [
                        ],
                    ]
                ],
                'title'      => $updateStock['title'],
                'updated_at' => $faker->dateTimeThisDecade,
                'url'        => 'https://qiita.com/yaotti/items/4bd431809afb1bb99e4f',
                'user'       => [
                    'description'         => 'Hello, world.',
                    'facebook_id'         => '',
                    'followees_count'     => 100,
                    'followers_count'     => 200,
                    'github_login_name'   => '',
                    'id'                  => $updateStock['user_id'],
                    'items_count'         => 300,
                    'linkedin_id'         => '',
                    'location'            => 'Tokyo, Japan',
                    'name'                => '',
                    'organization'        => 'test Inc',
                    'permanent_id'        => 1,
                    'profile_image_url'   => $updateStock['profile_image_url'],
                    'team_only'           => false,
                    'twitter_screen_name' => '',
                    'website_url'         => '',
                ],
                'page_views_count' => null
            ];

        return $stock;
    }

    /**
     * 異常系のテスト
     * Authorizationが存在しない場合エラーとなること
     */
    public function testErrorSessionNull()
    {
        $jsonResponse = $this->put(
            '/api/stocks'
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
     * ログインセッションが不正の場合エラーとなること
     */
    public function testErrorSessionNotFound()
    {
        $loginSession = 'notFound-2bae-4028-b53d-0f128479e650';

        $jsonResponse = $this->put(
            '/api/stocks',
            [],
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
    public function testErrorSessionIsExpired()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';

        factory(LoginSession::class)->create([
            'id'         => $loginSession,
            'account_id' => 1,
            'expired_on' => '2018-10-01 00:00:00'
        ]);

        $jsonResponse = $this->put(
            '/api/stocks',
            [],
            ['Authorization' => 'Bearer '.$loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 401;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => 'セッションの期限が切れました。再度、ログインしてください。']);
        $jsonResponse->assertStatus($expectedErrorCode);
        $jsonResponse->assertHeader('X-Request-Id');
    }
}
