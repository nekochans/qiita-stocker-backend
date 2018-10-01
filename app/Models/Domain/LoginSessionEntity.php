<?php
/**
 * LoginSessionEntity
 */

namespace App\Models\Domain;

/**
 * Class LoginSessionEntity
 * @package App\Models\Domain
 */
class LoginSessionEntity
{
    /**
     * アカウントID
     *
     * @var string
     */
    private $accountId;

    /**
     * セッションID
     *
     * @var string
     */
    private $sessionId;

    /**
     * LoginSessionEntity constructor.
     * @param LoginSessionEntityBuilder $builder
     */
    public function __construct(LoginSessionEntityBuilder $builder)
    {
        $this->accountId = $builder->getAccountId();
        $this->sessionId = $builder->getSessionId();
    }

    /**
     * @return string
     */
    public function getAccountId(): string
    {
        return $this->accountId;
    }

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }
}
