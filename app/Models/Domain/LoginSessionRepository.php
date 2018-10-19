<?php
/**
 * LoginSessionRepository
 */

namespace App\Models\Domain;

/**
 * Interface LoginSessionRepository
 * @package App\Models\Domain
 */
interface LoginSessionRepository
{
    /**
     * LoginSessionEntityを取得する
     *
     * @param string $sessionId
     * @return LoginSessionEntity
     */
    public function find(string $sessionId): LoginSessionEntity;

    /**
     * ログインセッションを削除する
     *
     * @param string $accountId
     */
    public function destroyLoginSessions(string $accountId);
}
