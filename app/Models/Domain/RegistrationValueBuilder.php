<?php
/**
 * RegistrationValueBuilder
 */

namespace App\Models\Domain;

/**
 * Class RegistrationValueBuilder
 * @package App\Models\Domain
 */
class RegistrationValueBuilder
{
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
     * @return RegistrationValue
     */
    public function build(): RegistrationValue
    {
        return new RegistrationValue($this);
    }
}
