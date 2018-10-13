<?php
/**
 * AccountTest
 */

namespace Tests\Feature;

use App\Eloquents\Account;
use App\Eloquents\AccessToken;
use App\Eloquents\LoginSession;
use App\Eloquents\QiitaAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class AccountTest
 * @package Tests
 */
class AccountTest extends AbstractTestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Account::truncate();
        LoginSession::truncate();
        AccessToken::truncate();
        QiitaAccount::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * 正常系のテスト
     * アカウントが作成できること
     */
    public function testSuccessCreate()
    {
        $accounts = factory(Account::class)->create();
        $accounts->each(function ($account) {
            factory(QiitaAccount::class)->create(['account_id' => $account->id]);
            factory(AccessToken::class)->create(['account_id' => $account->id]);
            factory(LoginSession::class)->create(['account_id' => $account->id]);
        });

        $permanentId = '123456';
        $accessToken = 'ea5d0a593b2655e9568f144fb1826342292f5c6b7d406fda00577b8d1530d8a5';

        $jsonResponse = $this->postJson(
            '/api/accounts',
            [
                'permanentId' => $permanentId,
                'accessToken' => $accessToken
            ]
        );

        $responseObject = json_decode($jsonResponse->content());

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedAccountId = 2;
        $expectedEmbedded = [
            'sessionId' => $responseObject->_embedded->sessionId
        ];
        $jsonResponse->assertJson(['accountId' => $expectedAccountId]);
        $jsonResponse->assertJson(['_embedded' => $expectedEmbedded]);
        $jsonResponse->assertStatus(201);

        // DBのテーブルに期待した形でデータが入っているか確認する
        $idSequence = 2;
        $this->assertDatabaseHas('accounts', [
            'id'           => $expectedAccountId,
            'lock_version' => 0,
        ]);

        $this->assertDatabaseHas('accounts_qiita_accounts', [
            'id'               => $idSequence,
            'account_id'       => $expectedAccountId,
            'qiita_account_id' => $permanentId,
            'lock_version'     => 0,
        ]);

        $this->assertDatabaseHas('accounts_access_tokens', [
            'id'           => $idSequence,
            'account_id'   => $expectedAccountId,
            'access_token' => $accessToken,
            'lock_version' => 0,
        ]);

        $this->assertDatabaseHas('login_sessions', [
            'id'           => $responseObject->_embedded->sessionId,
            'account_id'   => $expectedAccountId,
            'lock_version' => 0,
        ]);
    }
}
