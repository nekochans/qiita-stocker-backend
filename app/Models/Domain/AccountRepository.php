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
}
