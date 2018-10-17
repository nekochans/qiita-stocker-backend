<?php
/**
 * LoginSessionTest
 */

namespace Tests\Feature;

use App\Eloquents\Account;
use App\Eloquents\AccessToken;
use App\Eloquents\LoginSession;
use App\Eloquents\QiitaAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class LoginSessionTest
 * @package Tests\Feature
 */
class LoginSessionTest extends AbstractTestCase
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
     * ログインできること
     */
    public function testSuccessLogin()
    {
        $permanentId = '1';
        $accessToken = 'login0593b2655e9568f144fb1826342292f5c6b7d406fda00577b8d1530d8a5';

        $jsonResponse = $this->postJson(
            '/api/login-sessions',
            [
                'permanentId' => $permanentId,
                'accessToken' => $accessToken
            ]
        );

        $responseObject = json_decode($jsonResponse->content());

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $accountId = 1;
        $jsonResponse->assertJson(['sessionId' => $responseObject->sessionId]);
        $jsonResponse->assertStatus(201);

        // DBのテーブルに期待した形でデータが入っているか確認する
        $idSequence = 1;

        $this->assertDatabaseHas('accounts_qiita_accounts', [
            'id'               => $idSequence,
            'account_id'       => $accountId,
            'qiita_account_id' => $permanentId,
            'lock_version'     => 0,
        ]);

        $this->assertDatabaseHas('accounts_access_tokens', [
            'id'           => $idSequence,
            'account_id'   => $accountId,
            'access_token' => $accessToken,
            'lock_version' => 0,
        ]);

        $this->assertDatabaseHas('login_sessions', [
            'id'           => $responseObject->sessionId,
            'account_id'   => $accountId,
            'lock_version' => 0,
        ]);
    }

    /**
     * 異常系のテスト
     * アカウントが作成されていなかった場合エラーになること
     */
    public function testErrorLogin()
    {
        $permanentId = '2';
        $accessToken = 'login0593b2655e9568f144fb1826342292f5c6b7d406fda00577b8d1530d8a5';

        $jsonResponse = $this->postJson(
            '/api/login-sessions',
            [
                'permanentId' => $permanentId,
                'accessToken' => $accessToken
            ]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 404;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => 'アカウントが登録されていません。アカウント作成ページよりアカウントを作成してください。']);
        $jsonResponse->assertStatus($expectedErrorCode);
    }
}
