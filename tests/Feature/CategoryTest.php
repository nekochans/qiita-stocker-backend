<?php
/**
 * CategoryTest
 */

namespace Tests\Feature;

use App\Eloquents\Account;
use App\Eloquents\AccessToken;
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
        });
    }

    /**
     * 正常系のテスト
     * カテゴリが作成できること
     */
    public function testSuccessCreate()
    {
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => 1, ]);

        $categoryName = 'テストカテゴリ名';

        $jsonResponse = $this->postJson(
            '/api/categories',
            ['name'          => $categoryName],
            ['Authorization' => 'Bearer '.$loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedCategoryId = 1;
        $jsonResponse->assertJson(['categoryId' => $expectedCategoryId]);
        $jsonResponse->assertJson(['name' => $categoryName]);
        $jsonResponse->assertStatus(201);
        $jsonResponse->assertHeader('X-Request-Id');

        // TODO Repository実装後にDBのテストケースを追加する
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
}
