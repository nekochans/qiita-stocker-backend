<?php
/**
 * AccountRepository
 */

namespace App\Models\Domain;

/**
 * Interface AccountRepository
 * @package App\Models\Domain
 */
interface AccountRepository
{
    /**
     * アカウントを作成する
     *
     * @param QiitaAccountValue $qiitaAccountValue
     * @return AccountEntity
     */
    public function create(QiitaAccountValue $qiitaAccountValue): AccountEntity;

    /**
     * ログインセッションを保存する
     *
     * @param LoginSessionEntity $loginSessionEntity
     * @return mixed
     */
    public function saveLoginSession(LoginSessionEntity $loginSessionEntity);

    /**
     * パーマネントIDからアカウントを取得する
     *
     * @param QiitaAccountValue $qiitaAccountValue
     * @return AccountEntity
     * @throws \Exception
     */
    public function findByPermanentId(QiitaAccountValue $qiitaAccountValue): AccountEntity;

    /**
     * アクセストークンを更新する
     *
     * @param AccountEntity $accountEntity
     * @param QiitaAccountValue $qiitaAccountValue
     */
    public function updateAccessToken(AccountEntity $accountEntity, QiitaAccountValue $qiitaAccountValue);

    /**
     * アカウントを取得する
     *
     * @param string $accountId
     * @return AccountEntity
     */
    public function find(string $accountId): AccountEntity;
}
