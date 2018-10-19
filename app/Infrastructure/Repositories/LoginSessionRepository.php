<?php
/**
 * LoginSessionRepository
 */

namespace App\Infrastructure\Repositories;

use App\Eloquents\LoginSession;
use App\Models\Domain\LoginSessionEntity;
use App\Models\Domain\LoginSessionEntityBuilder;

/**
 * Class LoginSessionRepository
 * @package App\Infrastructure\Repositories
 */
class LoginSessionRepository implements \App\Models\Domain\LoginSessionRepository
{
    /**
     * LoginSessionEntityを取得する
     *
     * @param string $sessionId
     * @return LoginSessionEntity
     */
    public function find(string $sessionId): LoginSessionEntity
    {
        $loginSession = LoginSession::findOrFail($sessionId);

        $builder = new LoginSessionEntityBuilder();
        $builder->setSessionId($loginSession->id);
        $builder->setAccountId($loginSession->account_id);
        $builder->setExpiredOn(new \DateTime($loginSession->expired_on));

        return $builder->build();
    }

    /**
     * ログインセッションを削除する
     *
     * @param string $accountId
     */
    public function destroyLoginSessions(string $accountId)
    {
        LoginSession::where('account_id', $accountId)->delete();
    }
}
