<?php
/**
 * QiitaAccountValue
 */

namespace App\Models\Domain;

/**
 * Class QiitaAccountValue
 * @package App\Models\Domain
 */
class QiitaAccountValue
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
     * QiitaAccountValue constructor.
     * @param QiitaAccountValueBuilder $builder
     */
    public function __construct(QiitaAccountValueBuilder $builder)
    {
        $this->permanentId = $builder->getPermanentId();
        $this->accessToken = $builder->getAccessToken();
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

    /**
     * permanentIDからAccountEntityを取得する
     *
     * @param AccountRepository $accountRepository
     * @return AccountEntity
     */
    public function findAccountEntityByPermanentId(AccountRepository $accountRepository): AccountEntity
    {
        try {
            return $accountRepository->findByPermanentId($this);
        } catch (\Exception $e) {
            throw new \RuntimeException();
        }
    }

    /**
     * アカウントが作成済みか確認する
     *
     * @param AccountRepository $accountRepository
     * @return bool
     */
    public function isCreatedAccount(AccountRepository $accountRepository): bool
    {
        try {
            $accountRepository->findByPermanentId($this);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
