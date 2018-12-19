<?php
/**
 * LoginSessionCreateTest
 */

namespace Tests\Feature;

use App\Eloquents\Account;
use App\Eloquents\AccessToken;
use App\Eloquents\LoginSession;
use App\Eloquents\QiitaAccount;
use App\Eloquents\QiitaUserName;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class LoginSessionCreateTest
 * @package Tests\Feature
 */
class LoginSessionCreateTest extends AbstractTestCase
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
        });
    }

    /**
     * 正常系のテスト
     * ログインできること
     */
    public function testSuccessLogin()
    {
        $accountId = 2;
        $permanentId = '123';
        $userName = 'not-changed-test-user';
        $accessToken = 'login0593b2655e9568f144fb1826342292f5c6b7d406fda00577b8d1530d8a5';

        factory(Account::class)->create();
        factory(QiitaAccount::class)->create(['account_id' => $accountId, 'qiita_account_id' => $permanentId]);
        factory(QiitaUserName::class)->create(['account_id' => $accountId, 'user_name' => $userName]);
        factory(AccessToken::class)->create(['account_id' => $accountId]);
        factory(LoginSession::class)->create(['account_id' => $accountId]);

        $jsonResponse = $this->postJson(
            '/api/login-sessions',
            [
                'permanentId'    => $permanentId,
                'qiitaAccountId' => $userName,
                'accessToken'    => $accessToken
            ]
        );

        $responseObject = json_decode($jsonResponse->content());

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $jsonResponse->assertJson(['sessionId' => $responseObject->sessionId]);
        $jsonResponse->assertStatus(201);
        $jsonResponse->assertHeader('X-Request-Id');

        // DBのテーブルに期待した形でデータが入っているか確認する
        $idSequence = 2;

        $this->assertDatabaseHas('accounts_qiita_accounts', [
            'id'               => $idSequence,
            'account_id'       => $accountId,
            'qiita_account_id' => $permanentId,
            'lock_version'     => 0,
        ]);

        $this->assertDatabaseHas('accounts_qiita_user_names', [
            'id'               => $idSequence,
            'account_id'       => $accountId,
            'user_name'        => $userName,
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
     * 正常系のテスト
     * ログインできること
     * ユーザ名が更新されているケース
     */
    public function testSuccessLoginChangedUserName()
    {
        $permanentId = '1';
        $userName = 'changed-test-user';
        $accessToken = 'login0593b2655e9568f144fb1826342292f5c6b7d406fda00577b8d1530d8a5';

        $jsonResponse = $this->postJson(
            '/api/login-sessions',
            [
                'permanentId'    => $permanentId,
                'qiitaAccountId' => $userName,
                'accessToken'    => $accessToken
            ]
        );

        $responseObject = json_decode($jsonResponse->content());

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $accountId = 1;
        $jsonResponse->assertJson(['sessionId' => $responseObject->sessionId]);
        $jsonResponse->assertStatus(201);
        $jsonResponse->assertHeader('X-Request-Id');

        // DBのテーブルに期待した形でデータが入っているか確認する
        $idSequence = 1;

        $this->assertDatabaseHas('accounts_qiita_accounts', [
            'id'               => $idSequence,
            'account_id'       => $accountId,
            'qiita_account_id' => $permanentId,
            'lock_version'     => 0,
        ]);

        $this->assertDatabaseHas('accounts_qiita_user_names', [
            'id'               => $idSequence,
            'account_id'       => $accountId,
            'user_name'        => $userName,
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
        $userName = 'test-user';
        $accessToken = 'login0593b2655e9568f144fb1826342292f5c6b7d406fda00577b8d1530d8a5';

        $jsonResponse = $this->postJson(
            '/api/login-sessions',
            [
                'permanentId'    => $permanentId,
                'qiitaAccountId' => $userName,
                'accessToken'    => $accessToken
            ]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 404;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => 'アカウントが登録されていません。アカウント作成ページよりアカウントを作成してください。']);
        $jsonResponse->assertStatus($expectedErrorCode);
        $jsonResponse->assertHeader('X-Request-Id');
    }

    /**
     * 異常系のテスト
     * ログイン時のアクセストークンのバリデーション
     *
     * @param $accessToken
     * @dataProvider accessTokenProvider
     */
    public function testErrorLoginAccessTokenValidation($accessToken)
    {
        $permanentId = '123456';
        $userName = 'test-user';

        $jsonResponse = $this->postJson(
            '/api/login-sessions',
            [
                'permanentId'    => $permanentId,
                'qiitaAccountId' => $userName,

                'accessToken' => $accessToken
            ]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 422;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => '不正なリクエストが行われました。再度、ログインしてください。']);
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
     * ログイン時のパーマネントIDのバリデーション
     *
     * @param $permanentId
     * @dataProvider permanentIdProvider
     */
    public function testErrorLoginPermanentIdValidation($permanentId)
    {
        $userName = 'test-user';
        $accessToken = 'ea5d0a593b2655e9568f144fb1826342292f5c6b7d406fda00577b8d1530d8a5';

        $jsonResponse = $this->postJson(
            '/api/login-sessions',
            [
                'permanentId'    => $permanentId,
                'qiitaAccountId' => $userName,
                'accessToken'    => $accessToken
            ]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 422;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => '不正なリクエストが行われました。再度、ログインしてください。']);
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

    /**
     * 異常系のテスト
     * ログイン作成時のユーザ名のバリデーション
     *
     * @param $userName
     * @dataProvider userNameProvider
     */
    public function testErrorLoginUserNameValidation($userName)
    {
        $accessToken = 'ea5d0a593b2655e9568f144fb1826342292f5c6b7d406fda00577b8d1530d8a5';
        $permanentId = '123456';

        $jsonResponse = $this->postJson(
            '/api/login-sessions',
            [
                'permanentId'    => $permanentId,
                'qiitaAccountId' => $userName,
                'accessToken'    => $accessToken
            ]
        );

        // 実際にJSONResponseに期待したデータが含まれているか確認する
        $expectedErrorCode = 422;
        $jsonResponse->assertJson(['code' => $expectedErrorCode]);
        $jsonResponse->assertJson(['message' => '不正なリクエストが行われました。再度、ログインしてください。']);
        $jsonResponse->assertStatus($expectedErrorCode);
        $jsonResponse->assertHeader('X-Request-Id');
    }

    /**
     * ユーザ名のデータプロバイダ
     *
     * @return array
     */
    public function userNameProvider()
    {
        return [
            'emptyString'        => [''],
            'null'               => [null],
            'emptyArray'         => [[]],
            'tooLongLength'      => [str_repeat('a', 192)]
        ];
    }
}
