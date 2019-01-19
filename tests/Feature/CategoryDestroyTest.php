<?php
/**
 * CategoryDestroyTest
 */

namespace Tests\Feature;

use App\Eloquents\Account;
use App\Eloquents\Category;
use App\Eloquents\AccessToken;
use App\Eloquents\CategoryName;
use App\Eloquents\LoginSession;
use App\Eloquents\QiitaAccount;
use App\Eloquents\CategoryStock;
use App\Eloquents\QiitaUserName;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class CategoryDestroyTest
 * @package Tests\Feature
 */
class CategoryDestroyTest extends AbstractTestCase
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
     * カテゴリを削除できること
     */
    public function testSuccess()
    {
        $otherAccountId = 2;
        $otherCategoryId = 2;
        factory(Account::class)->create();
        factory(QiitaAccount::class)->create(['qiita_account_id' => 2, 'account_id' => $otherAccountId]);
        factory(QiitaUserName::class)->create(['account_id' => $otherAccountId]);
        factory(AccessToken::class)->create(['account_id' => $otherAccountId]);
        factory(LoginSession::class)->create(['account_id' => $otherAccountId]);
        factory(Category::class)->create(['account_id' => $otherAccountId]);
        factory(CategoryName::class)->create(['category_id' => $otherCategoryId]);
        factory(CategoryStock::class)->create(['category_id' => $otherCategoryId]);

        $categoryId = 1;
        $accountId = 1;
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);

        $jsonResponse = $this->delete(
            '/api/categories/'. $categoryId,
            [],
            ['Authorization' => 'Bearer '.$loginSession]
        );

        $jsonResponse->assertStatus(204);
        $jsonResponse->assertHeader('X-Request-Id');

        // DBのテーブルに期待した形でデータが入っているか確認する
        $this->assertDatabaseMissing('categories', [
            'account_id'   => $accountId,
        ]);
        $this->assertDatabaseHas('categories', [
            'account_id'   => $otherAccountId,
        ]);

        $this->assertDatabaseMissing('categories_names', [
            'category_id'   => $categoryId,
        ]);
        $this->assertDatabaseHas('categories_names', [
            'category_id'   => $otherCategoryId,
        ]);

        $this->assertDatabaseMissing('categories_stocks', [
            'category_id'   => $categoryId,
        ]);
        $this->assertDatabaseHas('categories_stocks', [
            'category_id'   => $otherCategoryId,
        ]);
    }

    /**
     * 異常系のテスト
     * カテゴリが見つからない場合エラーとなること
     */
    public function testErrorCategoryIdNotFound()
    {
        $otherAccountId = 2;
        $otherCategoryId = 2;

        factory(Account::class)->create();
        factory(QiitaAccount::class)->create(['qiita_account_id' => 2, 'account_id' => $otherAccountId]);
        factory(QiitaUserName::class)->create(['account_id' => $otherAccountId]);
        factory(AccessToken::class)->create(['account_id' => $otherAccountId]);
        factory(LoginSession::class)->create(['account_id' => $otherAccountId]);
        factory(Category::class)->create(['account_id' => $otherAccountId]);
        factory(CategoryName::class)->create(['category_id' => $otherCategoryId]);
        factory(CategoryStock::class)->create(['category_id' => $otherCategoryId]);

        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);

        $jsonResponse = $this->delete(
            '/api/categories/'. $otherCategoryId,
            [],
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 404;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => '不正なリクエストが行われました。']);
        $jsonResponse->assertStatus($expectedErrorCode);
        $jsonResponse->assertHeader('X-Request-Id');

        // DBのテーブルに期待した形でデータが入っているか確認する
        $this->assertDatabaseHas('categories', [
            'account_id'   => $otherAccountId,
        ]);

        $this->assertDatabaseHas('categories_names', [
            'category_id'   => $otherCategoryId,
        ]);

        $this->assertDatabaseHas('categories_stocks', [
            'category_id'   => $otherCategoryId,
        ]);
    }

    /**
     * 異常系のテスト
     * Authorizationが存在しない場合エラーとなること
     */
    public function testErrorLoginSessionNull()
    {
        $categoryId = 1;
        $jsonResponse = $this->delete('/api/categories/'. $categoryId);

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

        $jsonResponse = $this->delete(
            '/api/categories/'. $categoryId,
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
    public function testErrorLoginSessionIsExpired()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $categoryId = 1;
        factory(LoginSession::class)->create([
            'id'         => $loginSession,
            'account_id' => 1,
            'expired_on' => '2018-10-01 00:00:00'
        ]);

        $jsonResponse = $this->delete(
            '/api/categories/'. $categoryId,
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

    /**
     * 異常系のテスト
     * カテゴリ更新時のカテゴリIDのバリデーション
     *
     * @param $categoryId
     * @dataProvider categoryIdProvider
     */
    public function testErrorCategoryIdValidation($categoryId)
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);

        $jsonResponse = $this->delete(
            '/api/categories/'. $categoryId,
            [],
            ['Authorization' => 'Bearer '.$loginSession]
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
        // カテゴリIDが設定されていない場合はルーティングされないので考慮しない
        return [
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
     * メンテナンス中の場合エラーとなること
     */
    public function testErrorMaintenance()
    {
        \Config::set('app.maintenance', true);
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';

        $jsonResponse = $this->delete(
            '/api/categories/'. 1,
            [],
            ['Authorization' => 'Bearer '.$loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 503;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => 'サービスはメンテナンス中です。']);
        $jsonResponse->assertStatus($expectedErrorCode);
    }
}
