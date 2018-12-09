<?php
/**
 * LoginSessionEntityBuilder
 */

namespace App\Models\Domain\LoginSession;

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
     * 有効期限切れになる日時
     *
     * @var \DateTime
     */
    private $expiredOn;

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
     * @return \DateTime
     */
    public function getExpiredOn(): \DateTime
    {
        return $this->expiredOn;
    }

    /**
     * @param \DateTime $expiredOn
     */
    public function setExpiredOn(\DateTime $expiredOn): void
    {
        $this->expiredOn = $expiredOn;
    }

    /**
     * @return LoginSessionEntity
     */
    public function build(): LoginSessionEntity
    {
        return new LoginSessionEntity($this);
    }
}
