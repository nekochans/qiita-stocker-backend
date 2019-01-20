<?php
/**
 * CategoryUpdateTest
 */

namespace Tests\Feature;

use App\Eloquents\Account;
use App\Eloquents\Category;
use App\Eloquents\AccessToken;
use App\Eloquents\CategoryName;
use App\Eloquents\LoginSession;
use App\Eloquents\QiitaAccount;
use App\Eloquents\QiitaUserName;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class CategoryUpdateTest
 * @package Tests\Feature
 */
class CategoryUpdateTest extends AbstractTestCase
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
        });
    }

    /**
     * 正常系のテスト
     * 指定したカテゴリの更新ができること
     */
    public function testSuccess()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);

        $categoryId = '1';
        $categoryName = 'テストカテゴリ名';

        $jsonResponse = $this->patchJson(
            '/api/categories/'. $categoryId,
            ['name'          => $categoryName],
            ['Authorization' => 'Bearer '.$loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $jsonResponse->assertJson(['categoryId' => $categoryId]);
        $jsonResponse->assertJson(['name' => $categoryName]);
        $jsonResponse->assertStatus(200);
        $jsonResponse->assertHeader('X-Request-Id');

        // DBのテーブルに期待した形でデータが入っているか確認する
        $idSequence = 1;
        $this->assertDatabaseHas('categories', [
            'id'               => $categoryId,
            'account_id'       => $accountId,
            'lock_version'     => 0,
        ]);

        $this->assertDatabaseHas('categories_names', [
            'id'                => $idSequence,
            'category_id'       => $categoryId,
            'name'              => $categoryName,
            'lock_version'      => 0,
        ]);
    }


    /**
     * 異常系のテスト
     * 指定したカテゴリがアカウントに紐づかない場合エラーとなること
     */
    public function testErrorCategoryNotFound()
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

        $jsonResponse = $this->patchJson(
            '/api/categories/'. $otherCategoryId,
            ['name'          => 'テストカテゴリ名'],
            ['Authorization' => 'Bearer '.$loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 404;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => '不正なリクエストが行われました。']);
        $jsonResponse->assertStatus($expectedErrorCode);
        $jsonResponse->assertHeader('X-Request-Id');

        // DBのテーブルに期待した形でデータが入っているか確認する
        $idSequence = 2;
        $this->assertDatabaseHas('categories', [
            'id'               => $otherCategoryId,
            'account_id'       => $otherAccountId,
            'lock_version'     => 0,
        ]);

        $this->assertDatabaseHas('categories_names', [
            'id'                => $idSequence,
            'category_id'       => $otherAccountId,
            'name'              => $otherCategoryName,
            'lock_version'      => 0,
        ]);
    }

    /**
     * 異常系のテスト
     * Authorizationが存在しない場合エラーとなること
     */
    public function testErrorLoginSessionNull()
    {
        $categoryId = 1;
        $jsonResponse = $this->patchJson(
            '/api/categories/'. $categoryId,
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
    public function testErrorLoginSessionNotFound()
    {
        $loginSession = 'notFound-2bae-4028-b53d-0f128479e650';

        $categoryId = 1;
        $jsonResponse = $this->patchJson(
            '/api/categories/'. $categoryId,
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
    public function testErrorLoginSessionIsExpired()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';

        factory(LoginSession::class)->create([
            'id'         => $loginSession,
            'account_id' => 1,
            'expired_on' => '2018-10-01 00:00:00'
        ]);

        $categoryId = 1;
        $jsonResponse = $this->patchJson(
            '/api/categories/'. $categoryId,
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
     * 異常系のテスト
     * カテゴリ更新時のカテゴリ名のバリデーション
     *
     * @param $categoryName
     * @dataProvider categoryNameProvider
     */
    public function testErrorNameValidation($categoryName)
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);

        $categoryId = 1;

        $jsonResponse = $this->patchJson(
            '/api/categories/'. $categoryId,
            ['name'          => $categoryName],
            ['Authorization' => 'Bearer '.$loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 422;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => 'カテゴリ名は最大50文字です。カテゴリ名を短くしてください。']);
        $jsonResponse->assertStatus($expectedErrorCode);
        $jsonResponse->assertHeader('X-Request-Id');
    }

    /**
     * カテゴリ名のデータプロバイダ
     *
     * @return array
     */
    public function categoryNameProvider()
    {
        return [
            'emptyString'            => [''],
            'null'                   => [null],
            'emptyArray'             => [[]],
            'tooLongLength'          => ['111111111122222222223333333333444444444455555555556'], //51文字
            'multiByteTooLongLength' => ['テストテストテストテストテストテストテストテストテストテストテストテストテストテストテストテストテス🐱'] //51文字
        ];
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

        $categoryName = 'テストカテゴリ名';

        $jsonResponse = $this->patchJson(
            '/api/categories/'. $categoryId,
            ['name'          => $categoryName],
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

        $jsonResponse = $this->patchJson(
            '/api/categories/1',
            ['name'          => 'テストカテゴリ名'],
            ['Authorization' => 'Bearer '.$loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 503;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => 'サービスはメンテナンス中です。']);
        $jsonResponse->assertStatus($expectedErrorCode);
    }
}
