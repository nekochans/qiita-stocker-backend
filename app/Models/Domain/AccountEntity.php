<?php
/**
 * AccountEntity
 */

namespace App\Models\Domain;

/**
 * Class AccountEntity
 * @package App\Models\Domain
 */
class AccountEntity
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
     * アクセストークン
     *
     * @var string
     */
    private $accessToken;

    /**
     * AccountEntity constructor.
     * @param AccountEntityBuilder $builder
     */
    public function __construct(AccountEntityBuilder $builder)
    {
        $this->accountId = $builder->getPermanentId();
        $this->permanentId = $builder->getAccountId();
        $this->accessToken = $builder->getAccessToken();
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
    public function getPermanentId(): string
    {
        return $this->permanentId;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }
}
