<?php
/**
 * AccountEntityBuilder
 */

namespace App\Models\Domain\Account;

/**
 * Class AccountEntityBuilder
 * @package App\Models\Domain
 */
class AccountEntityBuilder
{
    /**
     * アカウントID
     *
     * @var string
     */
    private $accountId;

    /**
     * パーマネントID
     *
     * @var string
     */
    private $permanentId;

    /**
     * ユーザ名
     *
     * @var string
     */
    private $userName;

    /**
     * アクセストークン
     *
     * @var string
     */
    private $accessToken;

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
    public function getPermanentId(): string
    {
        return $this->permanentId;
    }

    /**
     * @param string $permanentId
     */
    public function setPermanentId(string $permanentId): void
    {
        $this->permanentId = $permanentId;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return AccountEntity
     */
    public function build(): AccountEntity
    {
        return new AccountEntity($this);
    }
}
