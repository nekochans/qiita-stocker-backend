<?php
/**
 * LoginSessionRepository
 */

namespace App\Infrastructure\Repositories\Eloquent;

use App\Eloquents\LoginSession;
use App\Models\Domain\LoginSession\LoginSessionEntity;
use App\Models\Domain\LoginSession\LoginSessionEntityBuilder;

/**
 * Class LoginSessionRepository
 * @package App\Infrastructure\Repositories\Eloquent
 */
class LoginSessionRepository implements \App\Models\Domain\LoginSession\LoginSessionRepository
{
    /**
     * ログインセッションを保存する
     *
     * @param LoginSessionEntity $loginSessionEntity
     * @return mixed
     */
    public function save(LoginSessionEntity $loginSessionEntity)
    {
        $loginSession = new LoginSession();
        $loginSession->id = $loginSessionEntity->getSessionId();
        $loginSession->account_id = $loginSessionEntity->getAccountId();
        $loginSession->expired_on = $loginSessionEntity->getExpiredOn();

        $loginSession->save();
    }

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
     * アカウントに紐づくログインセッションを削除する
     *
     * @param string $accountId
     */
    public function destroyByAccountId(string $accountId)
    {
        LoginSession::where('account_id', $accountId)->delete();
    }

    /**
     * ログインセッションを削除する
     *
     * @param string $sessionId
     */
    public function destroy(string $sessionId)
    {
        LoginSession::find($sessionId)->delete();
    }
}
