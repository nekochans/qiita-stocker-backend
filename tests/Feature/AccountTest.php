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
        $accounts = factory(Account::class)->create();
        $accounts->each(function ($account) {
            factory(QiitaAccount::class)->create(['account_id' => $account->id]);
            factory(AccessToken::class)->create(['account_id' => $account->id]);
            factory(LoginSession::class)->create(['account_id' => $account->id]);
        });
    }

    /**
     * 正常系のテスト
     * アカウントが作成できること
     */
    public function testSuccessCreate()
    {
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
        $jsonResponse->assertHeader('X-Request-Id');

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

    /**
     * 異常系のテスト
     * アカウントが作成済みの場合エラーになること
     */
    public function testErrorCreate()
    {
        $permanentId = '1';
        $accessToken = 'ea5d0a593b2655e9568f144fb1826342292f5c6b7d406fda00577b8d1530d8a5';

        $jsonResponse = $this->postJson(
            '/api/accounts',
            [
                'permanentId' => $permanentId,
                'accessToken' => $accessToken
            ]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 409;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => '既にアカウントの登録が完了しています。']);
        $jsonResponse->assertStatus($expectedErrorCode);
        $jsonResponse->assertHeader('X-Request-Id');
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

    /**
     * 異常系のテスト
     * アカウント作成時のアクセストークンのバリデーション
     *
     * @param $accessToken
     * @dataProvider accessTokenProvider
     */
    public function testErrorCreateAccessTokenValidation($accessToken)
    {
        $permanentId = '123456';

        $jsonResponse = $this->postJson(
            '/api/accounts',
            [
                'permanentId' => $permanentId,
                'accessToken' => $accessToken
            ]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 422;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => '不正なリクエストが行われました。再度、アカウント登録を行なってください。']);
        $jsonResponse->assertStatus($expectedErrorCode);
        $jsonResponse->assertHeader('X-Request-Id');
    }

    /**
     * アクセストークンのデータプロバイダ
     *
     * @return array
     */
    public function accessTokenProvider()
    {
        return [
            'emptyString'       => [''],
            'null'              => [null],
            'emptyArray'        => [[]],
            'tooShortLength'    => ['ea5d0a593b2655e9568f144fb1826342292f5c6'], // 39文字
            'tooLongLength'     => ['ea5d0a593b2655e9568f144fb1826342292f5c6b7d406fda00577b8d1530d8a5a'],  //65文字
            'symbol'            => ['%a5d0a593b2655e9568f144fb1826342292f5c6b7d406fda00577b8d1530d8a5'],
            'multiByte'         => ['あa5d0a593b2655e9568f144fb1826342292f5c6b7d406fda00577b8d1530d8a'],
        ];
    }

    /**
     * 異常系のテスト
     * アカウント作成時のパーマネントIDのバリデーション
     *
     * @param $permanentId
     * @dataProvider permanentIdProvider
     */
    public function testErrorCreatePermanentIdValidation($permanentId)
    {
        $accessToken = 'ea5d0a593b2655e9568f144fb1826342292f5c6b7d406fda00577b8d1530d8a5';

        $jsonResponse = $this->postJson(
            '/api/accounts',
            [
                'permanentId' => $permanentId,
                'accessToken' => $accessToken
            ]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 422;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => '不正なリクエストが行われました。再度、アカウント登録を行なってください。']);
        $jsonResponse->assertStatus($expectedErrorCode);
        $jsonResponse->assertHeader('X-Request-Id');
    }

    /**
     * パーマネントIDのデータプロバイダ
     *
     * @return array
     */
    public function permanentIdProvider()
    {
        return [
            'emptyString'        => [''],
            'null'               => [null],
            'emptyArray'         => [[]],
            'string'             => ['a'],
            'symbol'             => ['1/'],
            'multiByte'          => ['１'],
            'negativeNumber'     => [-1],
            'double'             => [1.1],
            'lessThanMin'        => [0],
            'greaterThanMax'     => [4294967295],
        ];
    }
}
