<?php
/**
 * CategoryTest
 */

namespace Tests\Feature;

use App\Eloquents\Account;
use App\Eloquents\Category;
use App\Eloquents\AccessToken;
use App\Eloquents\CategoryName;
use App\Eloquents\LoginSession;
use App\Eloquents\QiitaAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class CategoryTest
 * @package Tests\Feature
 */
class CategoryTest extends AbstractTestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();
        $accounts = factory(Account::class)->create();
        $accounts->each(function ($account) {
            factory(QiitaAccount::class)->create(['account_id' => $account->id]);
            factory(AccessToken::class)->create(['account_id' => $account->id]);
            factory(LoginSession::class)->create(['account_id' => $account->id]);
            $categories = factory(Category::class)->create(['account_id' => $account->id]);
            $categories->each(function ($category) {
                factory(CategoryName::class)->create(['category_id' => $category->id]);
            });
        });
    }

    /**
     * 正常系のテスト
     * カテゴリが作成できること
     */
    public function testSuccessCreate()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);

        $categoryName = 'テストカテゴリ名';

        $jsonResponse = $this->postJson(
            '/api/categories',
            ['name'          => $categoryName],
            ['Authorization' => 'Bearer '.$loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedCategoryId = 2;
        $jsonResponse->assertJson(['categoryId' => $expectedCategoryId]);
        $jsonResponse->assertJson(['name' => $categoryName]);
        $jsonResponse->assertStatus(201);
        $jsonResponse->assertHeader('X-Request-Id');

        // DBのテーブルに期待した形でデータが入っているか確認する
        $idSequence = 2;
        $this->assertDatabaseHas('categories', [
            'id'               => $expectedCategoryId,
            'account_id'       => $accountId,
            'lock_version'     => 0,
        ]);

        $this->assertDatabaseHas('categories_names', [
            'id'                => $idSequence,
            'category_id'       => $expectedCategoryId,
            'name'              => $categoryName,
            'lock_version'      => 0,
        ]);
    }

    /**
     * 異常系のテスト
     * Authorizationが存在しない場合エラーとなること
     */
    public function testErrorDestroyLoginSessionNull()
    {
        $jsonResponse = $this->postJson(
            '/api/categories',
            ['name'          => 'テストカテゴリ名']
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
    public function testErrorDestroyLoginSessionNotFound()
    {
        $loginSession = 'notFound-2bae-4028-b53d-0f128479e650';

        $jsonResponse = $this->postJson(
            '/api/categories',
            ['name'          => 'テストカテゴリ名'],
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
    public function testErrorDestroyLoginSessionIsExpired()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';

        factory(LoginSession::class)->create([
            'id'         => $loginSession,
            'account_id' => 1,
            'expired_on' => '2018-10-01 00:00:00'
        ]);

        $jsonResponse = $this->postJson(
            '/api/categories',
            ['name'          => 'テストカテゴリ名'],
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
     * 正常系のテスト
     * カテゴリ一覧が取得できること
     */
    public function testSuccessIndex()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 2;
        $categories = [
            ['categoryId' => 2, 'name' => 'テストカテゴリ2'],
            ['categoryId' => 3, 'name' => 'テストカテゴリ3'],
            ['categoryId' => 4, 'name' => 'テストカテゴリ4']
        ];

        factory(Account::class)->create();
        factory(QiitaAccount::class)->create(['qiita_account_id' => 2, 'account_id' => $accountId]);
        factory(AccessToken::class)->create(['account_id' => $accountId]);
        factory(LoginSession::class)->create(['account_id' => $accountId, 'id' => $loginSession]);

        factory(Category::class, 3)->create(['account_id' => $accountId]);
        factory(CategoryName::class)->create(['category_id' => $categories[0]['categoryId'], 'name' => $categories[0]['name']]);
        factory(CategoryName::class)->create(['category_id' => $categories[1]['categoryId'], 'name' => $categories[1]['name']]);
        factory(CategoryName::class)->create(['category_id' => $categories[2]['categoryId'], 'name' => $categories[2]['name']]);

        $jsonResponse = $this->get(
            '/api/categories',
            ['Authorization' => 'Bearer '.$loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $jsonResponse->assertJson($categories);
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertHeader('X-Request-Id');
    }

    /**
     * 正常系のテスト
     * カテゴリ一覧が登録されていなかった場合エラーとならないこと
     */
    public function testSuccessIndexNotFound()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 2;

        factory(Account::class)->create();
        factory(QiitaAccount::class)->create(['qiita_account_id' => 2, 'account_id' => $accountId]);
        factory(AccessToken::class)->create(['account_id' => $accountId]);
        factory(LoginSession::class)->create(['account_id' => $accountId, 'id' => $loginSession]);

        $jsonResponse = $this->get(
            '/api/categories',
            ['Authorization' => 'Bearer '.$loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $jsonResponse->assertJson([]);
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertHeader('X-Request-Id');
    }

    /**
     * 異常系のテスト
     * Authorizationが存在しない場合エラーとなること
     */
    public function testErrorIndexDestroyLoginSessionNull()
    {
        $jsonResponse = $this->get(
            '/api/categories'
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
    public function testErrorIndexDestroyLoginSessionNotFound()
    {
        $loginSession = 'notFound-2bae-4028-b53d-0f128479e650';

        $jsonResponse = $this->get(
            '/api/categories',
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
    public function testErrorIndexDestroyLoginSessionIsExpired()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';

        factory(LoginSession::class)->create([
            'id'         => $loginSession,
            'account_id' => 1,
            'expired_on' => '2018-10-01 00:00:00'
        ]);

        $jsonResponse = $this->get(
            '/api/categories',
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
