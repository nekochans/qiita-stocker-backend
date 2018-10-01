<?php
/**
 * LoginSessionEntityBuilder
 */

namespace App\Models\Domain;

/**
 * Class LoginSessionEntityBuilder
 * @package App\Models\Domain
 */
class LoginSessionEntityBuilder
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
     * @return string
     */
    public function getAccountId(): string
    {
        return $this->accountId;
    }

    /**
     * @param string $accountId
     */
    public function setAccountId(string $accountId): void
    {
        $this->accountId = $accountId;
    }

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     */
    public function setSessionId(string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    /**
     * @return LoginSessionEntity
     */
    public function build(): LoginSessionEntity
    {
        return new LoginSessionEntity($this);
    }
}
