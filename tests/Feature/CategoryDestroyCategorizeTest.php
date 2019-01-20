<?php
/**
 * CategoryDestroyCategorizeTest
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
 * Class CategoryDestroyCategorizeTest
 * @package Tests\Feature
 */
class CategoryDestroyCategorizeTest extends AbstractTestCase
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
     */
    public function testSuccess()
    {
        $id = 1;
        $accountId = 1;
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $accountId, ]);

        $jsonResponse = $this->delete(
            '/api/categories/stocks/'. $id,
            [],
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $jsonResponse->assertStatus(204);
        $jsonResponse->assertHeader('X-Request-Id');
    }

    /**
     * 異常系のテスト
     * Authorizationが存在しない場合エラーとなること
     */
    public function testErrorLoginSessionNull()
    {
        $id = 1;
        $jsonResponse = $this->delete('/api/categories/stocks/'. $id);

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
        $id = 1;

        $jsonResponse = $this->delete(
            '/api/categories/stocks/'. $id,
            [],
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
        $id = 1;
        factory(LoginSession::class)->create([
            'id'         => $loginSession,
            'account_id' => 1,
            'expired_on' => '2018-10-01 00:00:00'
        ]);

        $jsonResponse = $this->delete(
            '/api/categories/stocks/'. $id,
            [],
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
     * メンテナンス中の場合エラーとなること
     */
    public function testErrorMaintenance()
    {
        \Config::set('app.maintenance', true);
        $id = 1;
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';
        $jsonResponse = $this->delete(
            '/api/categories/stocks/'. $id,
            [],
            ['Authorization' => 'Bearer ' . $loginSession]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 503;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => 'サービスはメンテナンス中です。']);
        $jsonResponse->assertStatus($expectedErrorCode);
    }
}
