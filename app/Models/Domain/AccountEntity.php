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
        $this->accountId = $builder->getAccountId();
        $this->permanentId = $builder->getPermanentId();
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

    /**
     * アクセストークンを更新する
     *
     * @param AccountRepository $accountRepository
     * @param QiitaAccountValue $qiitaAccountValue
     * @return AccountEntity
     */
    public function updateAccessToken(AccountRepository $accountRepository, QiitaAccountValue $qiitaAccountValue): AccountEntity
    {
        $accountRepository->updateAccessToken($this, $qiitaAccountValue);

        $accountEntityBuilder = new AccountEntityBuilder();
        $accountEntityBuilder->setAccountId($this->accountId);
        $accountEntityBuilder->setPermanentId($this->permanentId);
        $accountEntityBuilder->setAccessToken($qiitaAccountValue->getAccessToken());
        return $accountEntityBuilder->build();
    }

    /**
     * アカウントが作成済みの場合に使用するメッセージ
     *
     * @return string
     */
    public static function accountCreatedMessage(): string
    {
        return '既にアカウントの登録が完了しています。';
    }
}
