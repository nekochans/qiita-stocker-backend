<?php
/**
 * AccountDestroyTest
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
 * Class AccountDestroyTest
 * @package Tests\Feature
 */
class AccountDestroyTest extends AbstractTestCase
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
            $categories = factory(Category::class, 2)->create(['account_id' => $account->id]);
            $categories->each(function ($category) {
                factory(CategoryName::class)->create(['category_id' => $category->id]);
            });
        });
    }

    /**
     * 正常系のテスト
     * アカウントが削除できること
     */
    public function testSuccessDestroy()
    {
        $destroyedAccountId = 1;
        $accountId = 2;
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';

        factory(LoginSession::class)->create(['id' => $loginSession, 'account_id' => $destroyedAccountId, ]);

        factory(Account::class)->create();
        factory(QiitaAccount::class)->create(['qiita_account_id' => 2, 'account_id' => $accountId]);
        factory(AccessToken::class)->create(['account_id' => $accountId]);
        factory(LoginSession::class)->create(['account_id' => $accountId]);

        factory(Category::class)->create(['account_id' => $accountId]);
        factory(CategoryName::class)->create(['category_id' => 3]);

        $jsonResponse = $this->delete(
            '/api/accounts',
            [],
            ['Authorization' => 'Bearer '.$loginSession]

        );

        $jsonResponse->assertStatus(204);
        $jsonResponse->assertHeader('X-Request-Id');


        // DBのテーブルに期待した形でデータが入っているか確認する
        $this->assertDatabaseMissing('accounts', [
            'id'           => $destroyedAccountId,
        ]);
        $this->assertDatabaseHas('accounts', [
            'id'       => $accountId,
        ]);

        $this->assertDatabaseMissing('accounts_qiita_accounts', [
            'account_id'       => $destroyedAccountId,
        ]);
        $this->assertDatabaseHas('accounts_qiita_accounts', [
            'account_id'       => $accountId,
        ]);

        $this->assertDatabaseMissing('accounts_access_tokens', [
            'account_id'   => $destroyedAccountId,
        ]);
        $this->assertDatabaseHas('accounts_access_tokens', [
            'account_id'       => $accountId,
        ]);

        $this->assertDatabaseMissing('login_sessions', [
            'account_id'   => $destroyedAccountId,
        ]);
        $this->assertDatabaseHas('login_sessions', [
            'account_id'   => $accountId,
        ]);

        $this->assertDatabaseMissing('categories', [
            'account_id'   => $destroyedAccountId,
        ]);
        $this->assertDatabaseHas('categories', [
            'account_id'   => $accountId,
        ]);

        $this->assertDatabaseMissing('categories_names', [
            'category_id'   => 1,
        ]);
        $this->assertDatabaseMissing('categories_names', [
            'category_id'   => 2,
        ]);
        $this->assertDatabaseHas('categories_names', [
            'category_id'   => 3,
        ]);
    }

    /**
     * 異常系のテスト
     * Authorizationが存在しない場合エラーとなること
     */
    public function testErrorDestroyLoginSessionNull()
    {
        $jsonResponse = $this->delete('/api/accounts');

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

        $jsonResponse = $this->delete(
            '/api/accounts',
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
    public function testErrorDestroyLoginSessionIsExpired()
    {
        $destroyedAccountId = 1;
        $loginSession = '54518910-2bae-4028-b53d-0f128479e650';

        factory(LoginSession::class)->create([
            'id'         => $loginSession,
            'account_id' => $destroyedAccountId,
            'expired_on' => '2018-10-01 00:00:00'
        ]);

        $jsonResponse = $this->delete(
            '/api/accounts',
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
