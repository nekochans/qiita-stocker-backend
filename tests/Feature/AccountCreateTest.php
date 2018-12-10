<?php
/**
 * AccountCreateTest
 */

namespace Tests\Feature;

use App\Eloquents\Account;
use App\Eloquents\AccessToken;
use App\Eloquents\LoginSession;
use App\Eloquents\QiitaAccount;
use App\Eloquents\QiitaUserName;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class AccountCreateTest
 * @package Tests
 */
class AccountCreateTest extends AbstractTestCase
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
     * アカウントが作成できること
     */
    public function testSuccessCreate()
    {
        $permanentId = '123456';
        $userName = 'test-user';
        $accessToken = 'ea5d0a593b2655e9568f144fb1826342292f5c6b7d406fda00577b8d1530d8a5';

        $jsonResponse = $this->postJson(
            '/api/accounts',
            [
                'permanentId'    => $permanentId,
                'qiitaAccountId' => $userName,
                'accessToken'    => $accessToken
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

        $this->assertDatabaseHas('accounts_qiita_user_names', [
            'id'               => $idSequence,
            'account_id'       => $expectedAccountId,
            'user_name'        => $userName,
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
        $userName = 'test-user';
        $accessToken = 'ea5d0a593b2655e9568f144fb1826342292f5c6b7d406fda00577b8d1530d8a5';

        $jsonResponse = $this->postJson(
            '/api/accounts',
            [
                'permanentId'    => $permanentId,
                'qiitaAccountId' => $userName,
                'accessToken'    => $accessToken
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
     * 異常系のテスト
     * アカウント作成時のアクセストークンのバリデーション
     *
     * @param $accessToken
     * @dataProvider accessTokenProvider
     */
    public function testErrorCreateAccessTokenValidation($accessToken)
    {
        $permanentId = '123456';
        $userName = 'test-user';

        $jsonResponse = $this->postJson(
            '/api/accounts',
            [
                'permanentId'    => $permanentId,
                'qiitaAccountId' => $userName,
                'accessToken'    => $accessToken
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
        $userName = 'test-user';

        $jsonResponse = $this->postJson(
            '/api/accounts',
            [
                'permanentId'    => $permanentId,
                'qiitaAccountId' => $userName,
                'accessToken'    => $accessToken
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

    // TODO ユーザ名のバリデーションテスト
}
