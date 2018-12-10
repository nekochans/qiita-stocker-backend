<?php
/**
 * AccountRepository
 */

namespace App\Infrastructure\Repositories;

use App\Eloquents\Account;
use App\Eloquents\AccessToken;
use App\Eloquents\LoginSession;
use App\Eloquents\QiitaAccount;
use App\Eloquents\QiitaUserName;
use App\Models\Domain\QiitaAccountValue;
use App\Models\Domain\Account\AccountEntity;
use App\Models\Domain\Account\AccountEntityBuilder;
use App\Models\Domain\LoginSession\LoginSessionEntity;

/**
 * Class AccountRepository
 * @package App\Infrastructure\Repositories
 */
class AccountRepository implements \App\Models\Domain\Account\AccountRepository
{
    /**
     * アカウントを作成する
     *
     * @param QiitaAccountValue $qiitaAccountValue
     * @return AccountEntity
     */
    public function create(QiitaAccountValue $qiitaAccountValue): AccountEntity
    {
        $accountId = $this->saveAccounts();
        $this->saveQiitaAccounts($accountId, $qiitaAccountValue->getPermanentId());
        $this->saveQiitaUserNames($accountId, $qiitaAccountValue->getUserName());
        $this->saveAccessTokens($accountId, $qiitaAccountValue->getAccessToken());

        $accountEntityBuilder = new AccountEntityBuilder();
        $accountEntityBuilder->setAccountId($accountId);
        $accountEntityBuilder->setPermanentId($qiitaAccountValue->getPermanentId());
        $accountEntityBuilder->setUserName($qiitaAccountValue->getUserName());
        $accountEntityBuilder->setAccessToken($qiitaAccountValue->getAccessToken());
        return $accountEntityBuilder->build();
    }

    /**
     * accounts テーブルにデータを保存する
     * @return int
     */
    private function saveAccounts(): int
    {
        $account = new Account;
        $account->save();
        $accountId = $account->getAttribute('id');

        return $accountId;
    }

    /**
     * accounts_qiita_accounts テーブルにデータを保存する
     * @param int $accountId
     * @param string $permanentId
     */
    private function saveQiitaAccounts(int $accountId, string $permanentId)
    {
        $qiitaAccount = new QiitaAccount();
        $qiitaAccount->account_id = $accountId;
        $qiitaAccount->qiita_account_id = $permanentId;
        $qiitaAccount->save();
    }

    /**
     * accounts_qiita_user_names テーブルにデータを保存する
     * @param int $accountId
     * @param string $userName
     */
    private function saveQiitaUserNames(int $accountId, string $userName)
    {
        $qiitaUserName = new QiitaUserName();
        $qiitaUserName->account_id = $accountId;
        $qiitaUserName->user_name = $userName;
        $qiitaUserName->save();
    }

    /**
     * accounts_access_tokens テーブルにデータを保存する
     *
     * @param int $accountId
     * @param string $accessToken
     */
    private function saveAccessTokens(int $accountId, string $accessToken)
    {
        $accountAccessToken = new AccessToken();
        $accountAccessToken->account_id = $accountId;
        $accountAccessToken->access_token = $accessToken;
        $accountAccessToken->save();
    }

    /**
     * ログインセッションを保存する
     *
     * @param LoginSessionEntity $loginSessionEntity
     * @return mixed
     */
    public function saveLoginSession(LoginSessionEntity $loginSessionEntity)
    {
        $loginSession = new LoginSession();
        $loginSession->id = $loginSessionEntity->getSessionId();
        $loginSession->account_id = $loginSessionEntity->getAccountId();
        $loginSession->expired_on = $loginSessionEntity->getExpiredOn();

        $loginSession->save();
    }

    /**
     * パーマネントIDからアカウントを取得する
     *
     * @param QiitaAccountValue $qiitaAccountValue
     * @return AccountEntity
     */
    public function findByPermanentId(QiitaAccountValue $qiitaAccountValue): AccountEntity
    {
        $qiitaAccount = QiitaAccount::where('qiita_account_id', $qiitaAccountValue->getPermanentId())->firstOrFail();

        $qiitaUserName = QiitaUserName::where('account_id', $qiitaAccount->account_id)->firstOrFail();

        $accessToken = AccessToken::where('account_id', $qiitaAccount->account_id)->firstOrFail();

        $accountEntityBuilder = new AccountEntityBuilder();
        $accountEntityBuilder->setAccountId($qiitaAccount->account_id);
        $accountEntityBuilder->setPermanentId($qiitaAccountValue->getPermanentId());
        $accountEntityBuilder->setUserName($qiitaUserName->user_name);
        $accountEntityBuilder->setAccessToken($accessToken->access_token);

        return $accountEntityBuilder->build();
    }

    /**
     * アクセストークンを更新する
     *
     * @param AccountEntity $accountEntity
     * @param QiitaAccountValue $qiitaAccountValue
     */
    public function updateAccessToken(AccountEntity $accountEntity, QiitaAccountValue $qiitaAccountValue)
    {
        $accessToken = AccessToken::where('account_id', $accountEntity->getAccountId())->first();
        $accessToken->access_token = $qiitaAccountValue->getAccessToken();
        $accessToken->save();
    }

    /**
     * アカウントを取得する
     *
     * @param string $accountId
     * @return AccountEntity
     */
    public function find(string $accountId): AccountEntity
    {
        $qiitaAccount = QiitaAccount::where('account_id', $accountId)->firstOrFail();
        $qiitaUserName = QiitaUserName::where('account_id', $accountId)->firstOrFail();
        $accessToken = AccessToken::where('account_id', $accountId)->firstOrFail();

        $accountEntityBuilder = new AccountEntityBuilder();
        $accountEntityBuilder->setAccountId($accountId);
        $accountEntityBuilder->setPermanentId($qiitaAccount->qiita_account_id);
        $accountEntityBuilder->setUserName($qiitaUserName->user_name);
        $accountEntityBuilder->setAccessToken($accessToken->access_token);

        return $accountEntityBuilder->build();
    }

    /**
     * Qiitaアカウントを削除する
     *
     * @param string $accountId
     */
    public function destroyQiitaAccount(string $accountId)
    {
        QiitaAccount::where('account_id', $accountId)->delete();
    }

    /**
     * Qiitaユーザ名を削除する
     *
     * @param string $accountId
     */
    public function destroyQiitaUserName(string $accountId)
    {
        QiitaUserName::where('account_id', $accountId)->delete();
    }

    /**
     * アクセストークンを削除する
     *
     * @param string $accountId
     */
    public function destroyAccessToken(string $accountId)
    {
        AccessToken::where('account_id', $accountId)->delete();
    }

    /**
     * アカウントを削除する
     *
     * @param string $accountId
     */
    public function destroyAccount(string $accountId)
    {
        Account::destroy($accountId);
    }
}
