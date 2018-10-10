<?php
/**
 * AccountRepository
 */

namespace App\Infrastructure\Repositories;

use App\Eloquents\Account;
use App\Eloquents\AccessToken;
use App\Eloquents\LoginSession;
use App\Eloquents\QiitaAccount;
use App\Models\Domain\AccountEntity;
use App\Models\Domain\QiitaAccountValue;
use App\Models\Domain\LoginSessionEntity;
use App\Models\Domain\AccountEntityBuilder;

/**
 * Class AccountRepository
 * @package App\Infrastructure\Repositories
 */
class AccountRepository implements \App\Models\Domain\AccountRepository
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
        $this->saveAccessTokens($accountId, $qiitaAccountValue->getAccessToken());

        $accountEntityBuilder = new AccountEntityBuilder();
        $accountEntityBuilder->setAccountId($accountId);
        $accountEntityBuilder->setPermanentId($qiitaAccountValue->getPermanentId());
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
     * アカウントを取得する
     *
     * @param QiitaAccountValue $qiitaAccountValue
     * @return AccountEntity
     * @throws \Exception
     */
    public function findByPermanentId(QiitaAccountValue $qiitaAccountValue): AccountEntity
    {
        $qiitaAccount = QiitaAccount::where('qiita_account_id', $qiitaAccountValue->getPermanentId())->first();

        if ($qiitaAccount === null) {
            throw new \Exception('error');
        }

        $accountEntityBuilder = new AccountEntityBuilder();
        $accountEntityBuilder->setAccountId($qiitaAccount->account_id);
        $accountEntityBuilder->setPermanentId($qiitaAccountValue->getPermanentId());
        $accountEntityBuilder->setAccessToken($qiitaAccountValue->getAccessToken());

        return $accountEntityBuilder->build();
    }

    /**
     * アクセストークンを更新する
     *
     * @param AccountEntity $accountEntity
     */
    public function updateAccessToken(AccountEntity $accountEntity)
    {
        $accessToken = AccessToken::where('account_id', $accountEntity->getAccountId())->first();
        $accessToken->access_token = $accountEntity->getAccessToken();
        $accessToken->save();
    }
}
