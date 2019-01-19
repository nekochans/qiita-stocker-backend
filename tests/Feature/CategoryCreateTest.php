<?php
/**
 * CategoryCreateTest
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
 * Class CategoryCreateTest
 * @package Tests\Feature
 */
class CategoryCreateTest extends AbstractTestCase
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
    public function testErrorLoginSessionNull()
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
    public function testErrorLoginSessionNotFound()
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
    public function testErrorLoginSessionIsExpired()
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
     * 異常系のテスト
     * カテゴリ作成時のカテゴリ名のバリデーション
     *
     * @param $categoryName
     * @dataProvider categoryNameProvider
     */
    public function testErrorCreateValidation($categoryName)
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $accountId = 1;
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId]);

        $jsonResponse = $this->postJson(
            '/api/categories',
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
     * メンテナンス中の場合エラーとなること
     */
    public function testErrorMaintenance()
    {
        \Config::set('app.maintenance', true);
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';

        $jsonResponse = $this->postJson(
            '/api/categories',
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
