<?php
/**
 * RegistrationRepository
 */

namespace App\Infrastructure\Repositories;

use App\Models\Domain\AccountEntity;
use App\Models\Domain\QiitaAccountValue;
use App\Models\Domain\LoginSessionEntity;
use App\Models\Domain\AccountEntityBuilder;

/**
 * Class RegistrationRepository
 * @package App\Infrastructure\Repositories
 */
class RegistrationRepository implements \App\Models\Domain\RegistrationRepository
{
    /**
     * アカウントを作成する
     *
     * @param QiitaAccountValue $qiitaAccountValue
     * @return AccountEntity
     */
    public function createAccount(QiitaAccountValue $qiitaAccountValue): AccountEntity
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
        $accountId = 1;
        return $accountId;
    }

    /**
     * accounts_qiita_accounts テーブルにデータを保存する
     * @param int $accountId
     * @param string $permanentId
     */
    private function saveQiitaAccounts(int $accountId, string $permanentId)
    {
    }

    /**
     * accounts_access_tokens テーブルにデータを保存する
     *
     * @param int $accountId
     * @param string $accessToken
     */
    private function saveAccessTokens(int $accountId, string $accessToken)
    {
    }

    /**
     * ログインセッションを保存する
     *
     * @param LoginSessionEntity $loginSessionEntity
     * @return mixed
     */
    public function saveLoginSession(LoginSessionEntity $loginSessionEntity)
    {
    }
}
