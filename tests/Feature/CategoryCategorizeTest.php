<?php
/**
 * CategoryCategorizeTest
 */

namespace Tests\Feature;

use App\Eloquents\Account;
use App\Eloquents\Category;
use Faker\Factory as Faker;
use App\Eloquents\AccessToken;
use App\Eloquents\CategoryName;
use App\Eloquents\LoginSession;
use App\Eloquents\QiitaAccount;
use App\Eloquents\CategoryStock;
use App\Eloquents\QiitaUserName;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class CategoryCategorizeTest
 * @package Tests\Feature
 */
class CategoryCategorizeTest extends AbstractTestCase
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
                factory(CategoryStock::class)->create(['category_id' => $category->id]);
            });
        });
    }

    /**
     * 正常系のテスト
     * カテゴリとストックのリレーションが作成されること
     * 全て新規でストックを保存するケース
     */
    public function testSuccess()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);

        $stockList = $this->createStocks(10, 2);

        $mockData = [];
        $articleIds = [];
        foreach ($stockList as $stock) {
            array_push($articleIds, $stock['article_id']);

            $fetchStock = $this->createFetchStocksData($stock);
            array_push($mockData, [200, [], json_encode($fetchStock)]);
        }
        $this->setMockGuzzle($mockData);

        $categoryId = 1;
        $jsonResponse = $this->postJson(
            '/api/categories/stocks',
            [
                'id'         => $categoryId,
                'articleIds' => $articleIds
            ],
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $jsonResponse->assertStatus(201);
        $jsonResponse->assertHeader('X-Request-Id');

        // DBのテーブルに期待した形でデータが入っているか確認する
        foreach ($stockList as $stock) {
            $this->assertDatabaseHas('categories_stocks', [
                'id'                        => $stock['id'],
                'category_id'               => $categoryId,
                'article_id'                => $stock['article_id'],
                'title'                     => $stock['title'],
                'user_id'                   => $stock['user_id'],
                'profile_image_url'         => $stock['profile_image_url'],
                'article_created_at'        => $stock['article_created_at'],
                'tags'                      => json_encode($stock['tags']),
                'lock_version'              => 0
            ]);
        }
    }

    /**
     * 正常系のテスト
     *
     * ストックが他のカテゴリにカテゴライズ済みの場合、
     * 既存のリレーションが削除され、新しくリレーションが作成されていること
     */
    public function testSuccessRecategorize()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);

        $stockList = $this->createStocks(10, 12);

        // ストックが他のカテゴリにカテゴライズされているデータを作成
        $otherCategoryId = 2;
        $mockData = [];
        $articleIds = [];
        factory(Category::class)->create(['account_id' => $accountId]);
        factory(CategoryName::class)->create(['category_id' => $otherCategoryId]);

        foreach ($stockList as $stock) {
            factory(CategoryStock::class)->create(['category_id' => $otherCategoryId, 'article_id' => $stock['article_id']]);

            array_push($articleIds, $stock['article_id']);

            $fetchStock = $this->createFetchStocksData($stock);
            array_push($mockData, [200, [], json_encode($fetchStock)]);
        }

        $this->setMockGuzzle($mockData);

        $categoryId = 1;
        $jsonResponse = $this->postJson(
            '/api/categories/stocks',
            [
                'id'         => $categoryId,
                'articleIds' => $articleIds
            ],
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $jsonResponse->assertStatus(201);
        $jsonResponse->assertHeader('X-Request-Id');

        // DBのテーブルに期待した形でデータが入っているか確認する
        foreach ($stockList as $stock) {
            $this->assertDatabaseHas('categories_stocks', [
                'id'                        => $stock['id'],
                'category_id'               => $categoryId,
                'article_id'                => $stock['article_id'],
                'title'                     => $stock['title'],
                'user_id'                   => $stock['user_id'],
                'profile_image_url'         => $stock['profile_image_url'],
                'article_created_at'        => $stock['article_created_at'],
                'tags'                      => json_encode($stock['tags']),
                'lock_version'              => 0
            ]);
        }

        foreach ($stockList as $stock) {
            $this->assertDatabaseMissing('categories_stocks', [
                'category_id'               => $otherCategoryId,
                'article_id'                => $stock['article_id'],
                'title'                     => $stock['title'],
                'user_id'                   => $stock['user_id'],
                'profile_image_url'         => $stock['profile_image_url'],
                'article_created_at'        => $stock['article_created_at'],
                'tags'                      => json_encode($stock['tags']),
                'lock_version'              => 0
            ]);
        }
    }

    /**
     * 正常系のテスト
     *
     * ストックが指定されたカテゴリにカテゴライズ済みの場合、APIへのリクエストが行われないこと
     */
    public function testSuccessCategorized()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);

        $stockList = $this->createStocks(10, 2);

        // ストックが指定したカテゴリに登録ずみのデータを作成
        $categoryId = 1;
        $articleIds = [];
        foreach ($stockList as $stock) {
            factory(CategoryStock::class)->create(
                [
                'category_id'               => $categoryId,
                'article_id'                => $stock['article_id'],
                'title'                     => $stock['title'],
                'user_id'                   => $stock['user_id'],
                'profile_image_url'         => $stock['profile_image_url'],
                'article_created_at'        => $stock['article_created_at'],
                'tags'                      => json_encode($stock['tags'])
                ]
            );
            array_push($articleIds, $stock['article_id']);
        }

        $jsonResponse = $this->postJson(
            '/api/categories/stocks',
            [
                'id'         => $categoryId,
                'articleIds' => $articleIds
            ],
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $jsonResponse->assertStatus(201);
        $jsonResponse->assertHeader('X-Request-Id');

        // DBのテーブルに期待した形でデータが入っているか確認する
        foreach ($stockList as $stock) {
            $this->assertDatabaseHas('categories_stocks', [
                'id'                        => $stock['id'],
                'category_id'               => $categoryId,
                'article_id'                => $stock['article_id'],
                'title'                     => $stock['title'],
                'user_id'                   => $stock['user_id'],
                'profile_image_url'         => $stock['profile_image_url'],
                'article_created_at'        => $stock['article_created_at'],
                'tags'                      => json_encode($stock['tags']),
                'lock_version'              => 0
            ]);
        }
    }

//    /**
//     * 異常系のテスト
//     * APIのレスポンスがエラーの場合、エラーとなること
//     */
//    public function testErrorApiFailure()
//    {
//        $errorResponse = [
//            'message' => 'Not found',
//            'type'    => 'not_found'
//        ];
//
//        $mockData = [[404, [], json_encode($errorResponse)], [404, [], json_encode($errorResponse)]];
//        $this->setMockGuzzle($mockData);
//
//        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
//        $accountId = 1;
//        $categoryId = 1;
//        $artcleId = 'aabbccddee0000000001';
//        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);
//
//        factory(Category::class)->create(['account_id' => $accountId]);
//        factory(CategoryName::class)->create(['category_id' => 2]);
//        factory(CategoryStock::class)->create(['category_id' => 2, 'article_id' => $artcleId]);
//
//        $jsonResponse = $this->postJson(
//            '/api/categories/stocks',
//            [
//                'id'         => $categoryId,
//                'articleIds' => [$artcleId, 'aabbccddee0000000002']
//            ],
//            ['Authorization' => 'Bearer ' . $loginSession]
//        );
//
//        // 実際にJSONResponseに期待したデータが含まれているか確認する
//        $expectedErrorCode = 503;
//        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
//        $jsonResponse->assertJson(['message' => 'Service Unavailable']);
//        $jsonResponse->assertStatus($expectedErrorCode);
//        $jsonResponse->assertHeader('X-Request-Id');
//    }

    /**
     * ストックのデータを作成する
     *
     * @param int $count
     * @param int $idSequence
     * @return array
     */
    private function createStocks(int $count, int $idSequence) :array
    {
        $stocks = [];
        for ($i = 0; $i < $count; $i++) {
            $secondTag = $i + 1;

            $stock = [
                'id'                        => $idSequence,
                'article_id'                => 'abcdefghij'. str_pad($i, 10, '0', STR_PAD_LEFT),
                'article_id'                => 'aabbccddee'. str_pad($i, 10, '0', STR_PAD_LEFT),
                'title'                     => 'title' . $i,
                'user_id'                   => 'user-id-' . $i,
                'profile_image_url'         => 'http://test.com/test-image-updated.jpag'. $i,
                'article_created_at'        => '2018-01-01 00:11:22.000000',
                'tags'                      => ['tag'. $i, 'tag'. $secondTag]
            ];
            array_push($stocks, $stock);
            $idSequence += 1;
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
     * カテゴリが見つからない場合エラーとなること
     */
    public function testErrorCategoryIdNotFound()
    {
        $otherAccountId = 2;
        $otherCategoryId = 2;
        $otherCategoryName = 'accountIDが2のカテゴリ';

        factory(Account::class)->create();
        factory(QiitaAccount::class)->create(['qiita_account_id' => 2, 'account_id' => $otherAccountId]);
        factory(QiitaUserName::class)->create(['account_id' => $otherAccountId]);
        factory(AccessToken::class)->create(['account_id' => $otherAccountId]);
        factory(LoginSession::class)->create(['account_id' => $otherAccountId]);
        factory(Category::class)->create(['account_id' => $otherAccountId]);
        factory(CategoryName::class)->create(['category_id' => $otherCategoryId, 'name' => $otherCategoryName]);

        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);

        $articleIds = ['d210ddc2cb1bfeea9331','d210ddc2cb1bfeea9332','d210ddc2cb1bfeea9333'];
        $jsonResponse = $this->postJson(
            '/api/categories/stocks',
            [
                'id'         => $otherCategoryId,
                'articleIds' => $articleIds
            ],
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 404;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => '不正なリクエストが行われました。']);
        $jsonResponse->assertStatus($expectedErrorCode);
        $jsonResponse->assertHeader('X-Request-Id');
    }

    /**
     * 異常系のテスト
     * Authorizationが存在しない場合エラーとなること
     */
    public function testErrorLoginSessionNull()
    {
        $categoryId = 1;
        $articleIds = ['d210ddc2cb1bfeea9331','d210ddc2cb1bfeea9332','d210ddc2cb1bfeea9333'];
        $jsonResponse = $this->postJson(
            '/api/categories/stocks',
            [
                'id'         => $categoryId,
                'articleIds' => $articleIds
            ]
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
    public function testErrorLoginSessionNotFound()
    {
        $loginSession = 'notFound-2bae-4028-b53d-0f128479e650';
        $categoryId = 1;
        $articleIds = ['d210ddc2cb1bfeea9331','d210ddc2cb1bfeea9332','d210ddc2cb1bfeea9333'];

        $jsonResponse = $this->postJson(
            '/api/categories/stocks',
            [
                'id'         => $categoryId,
                'articleIds' => $articleIds
            ],
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
        $categoryId = 1;
        $articleIds = ['d210ddc2cb1bfeea9331','d210ddc2cb1bfeea9332','d210ddc2cb1bfeea9333'];
        factory(LoginSession::class)->create([
            'id'         => $loginSession,
            'account_id' => 1,
            'expired_on' => '2018-10-01 00:00:00'
        ]);

        $jsonResponse = $this->postJson(
            '/api/categories/stocks',
            [
                'id'         => $categoryId,
                'articleIds' => $articleIds
            ],
            ['Authorization' => 'Bearer ' . $loginSession]
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
     * カテゴリIDのバリデーション
     *
     * @param $categoryId
     * @dataProvider categoryIdProvider
     */
    public function testErrorCategoryIdValidation($categoryId)
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);

        $articleIds = ['d210ddc2cb1bfeea9331','d210ddc2cb1bfeea9332','d210ddc2cb1bfeea9333'];
        $jsonResponse = $this->postJson(
            '/api/categories/stocks',
            [
                'id'         => $categoryId,
                'articleIds' => $articleIds
            ],
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
     * カテゴリIDのデータプロバイダ
     *
     * @return array
     */
    public function categoryIdProvider()
    {
        return [
            'emptyString'        => [''],
            'null'               => [null],
            'emptyArray'         => [[]],
            'string'             => ['a'],
            'symbol'             => ['1@'],
            'multiByte'          => ['１'],
            'negativeNumber'     => [-1],
            'double'             => [1.1],
            'lessThanMin'        => [0],
            'greaterThanMax'     => [18446744073709551615],
        ];
    }

    /**
     * 異常系のテスト
     * ArticleIDのバリデーション
     *
     * @param $articleId
     * @dataProvider articleIdProvider
     */
    public function testErrorArticleIdValidation($articleId)
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);

        $articleIds = ['d210ddc2cb1bfeea9331'];
        array_push($articleIds, $articleId);

        $jsonResponse = $this->postJson(
            '/api/categories/stocks',
            [
                'id'         => 1,
                'articleIds' => $articleIds
            ],
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
     * ArticleIDのデータプロバイダ
     *
     * @return array
     */
    public function articleIdProvider()
    {
        return [
            'emptyString'             => [''],
            'null'                    => [null],
            'emptyArray'              => [[]],
            'symbol'                  => ['a210ddc2cb1bfeea933@'],
            'multiByte'               => ['１１１１１１１１１１１１１１１１１１１１'],
            'tooShortLength'          => ['a210ddc2cb1bfeea933'],
            'tooLongLength'           => ['a210ddc2cb1bfeea93311'],
            'f-z'                     => ['gz10ddc2cb1bfeea9331']
        ];
    }

    /**
     * 異常系のテスト
     * ArticleIdsのバリデーション
     *
     * @param $articleIds
     * @dataProvider articleIdsProvider
     */
    public function testErrorArticleIdsValidation($articleIds)
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);

        $jsonResponse = $this->postJson(
            '/api/categories/stocks',
            [
                'id'         => 1,
                'articleIds' => $articleIds
            ],
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
     * ArticleIDのデータプロバイダ
     *
     * @return array
     */
    public function articleIdsProvider()
    {
        return [
            'emptyArray'                   => [[]],
            'emptyString'                  => [''],
            'notArray'                     => ['a210ddc2cb1bfeea9331'],
            'tooLongLengthArray'           => [array_fill(0, 21, 'a210ddc2cb1bfeea9332')]
        ];
    }

    /**
     * 異常系のテスト
     * メンテナンス中の場合エラーとなること
     */
    public function testErrorMaintenance()
    {
        \Config::set('app.maintenance', true);
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';

        $jsonResponse = $this->postJson(
            '/api/categories/stocks',
            [
                'id'         => 1,
                'articleIds' => ['d210ddc2cb1bfeea9331','d210ddc2cb1bfeea9332','d210ddc2cb1bfeea9333']
            ],
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 503;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => 'サービスはメンテナンス中です。']);
        $jsonResponse->assertStatus($expectedErrorCode);
    }
}
