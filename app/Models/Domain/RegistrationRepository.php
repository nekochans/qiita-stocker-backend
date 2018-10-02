<?php
/**
 * RegistrationRepository
 */

namespace App\Models\Domain;

/**
 * Interface RegistrationRepository
 * @package App\Models\Domain
 */
interface RegistrationRepository
{
    /**
     * アカウントを作成する
     *
     * @param QiitaAccountValue $qiitaAccountValue
     * @return AccountEntity
     */
    public function createAccount(QiitaAccountValue $qiitaAccountValue): AccountEntity;

    /**
     * ログインセッションを保存する
     *
     * @param LoginSessionEntity $loginSessionEntity
     * @return mixed
     */
    public function saveLoginSession(LoginSessionEntity $loginSessionEntity);
}
