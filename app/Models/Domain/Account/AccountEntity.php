<?php
/**
 * AccountEntity
 */

namespace App\Models\Domain\Account;

use App\Models\Domain\QiitaAccountValue;
use App\Models\Domain\Category\CategoryEntity;
use App\Models\Domain\Category\CategoryRepository;
use App\Models\Domain\LoginSession\LoginSessionRepository;

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
     * AccountEntity constructor.
     * @param AccountEntityBuilder $builder
     */
    public function __construct(AccountEntityBuilder $builder)
    {
        $this->accountId = $builder->getAccountId();
        $this->permanentId = $builder->getPermanentId();
        $this->userName = $builder->getUserName();
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
    public function getUserName(): string
    {
        return $this->userName;
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
        $accountEntityBuilder->setUserName($this->userName);
        $accountEntityBuilder->setPermanentId($this->permanentId);
        $accountEntityBuilder->setAccessToken($qiitaAccountValue->getAccessToken());
        return $accountEntityBuilder->build();
    }

    /**
     * @param QiitaAccountValue $qiitaAccountValue
     * @return bool
     */
    public function isChangedQiitaUserName(QiitaAccountValue $qiitaAccountValue): bool
    {
        if ($this->userName !== $qiitaAccountValue->getUserName()) {
            return true;
        }
        return false;
    }

    /**
     * ユーザ名を更新する
     *
     * @param AccountRepository $accountRepository
     * @param QiitaAccountValue $qiitaAccountValue
     * @return AccountEntity
     */
    public function updateQiitaUserName(AccountRepository $accountRepository, QiitaAccountValue $qiitaAccountValue): AccountEntity
    {
        $accountRepository->updateQiitaUserName($this, $qiitaAccountValue);

        $accountEntityBuilder = new AccountEntityBuilder();
        $accountEntityBuilder->setAccountId($this->accountId);
        $accountEntityBuilder->setUserName($qiitaAccountValue->getUserName());
        $accountEntityBuilder->setPermanentId($this->permanentId);
        $accountEntityBuilder->setAccessToken($this->accessToken);
        return $accountEntityBuilder->build();
    }

    /**
     * 退会する
     *
     * @param AccountRepository $accountRepository
     * @param LoginSessionRepository $loginSessionRepository
     * @param CategoryRepository $categoryRepository
     */
    public function cancel(
        AccountRepository $accountRepository,
        LoginSessionRepository $loginSessionRepository,
        CategoryRepository $categoryRepository
    ) {
        $categoryRepository->destroyAll($this->getAccountId());
        $loginSessionRepository->destroyLoginSessions($this->getAccountId());
        $accountRepository->destroyAccessToken($this->getAccountId());
        $accountRepository->destroyQiitaAccount($this->getAccountId());
        $accountRepository->destroyQiitaUserName($this->getAccountId());
        $accountRepository->destroyAccount($this->getAccountId());
    }

    /**
     * アカウントが持つCategoryEntityを取得する
     *
     * @param CategoryRepository $categoryRepository
     * @param string $categoryId
     * @return CategoryEntity
     */
    public function findHasCategoryEntity(CategoryRepository $categoryRepository, string $categoryId): CategoryEntity
    {
        return $categoryRepository->findByIdAndAccountId($categoryId, $this->getAccountId());
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

    /**
     * アカウントが作成されていなかった場合に使用するメッセージ
     *
     * @return string
     */
    public static function accountNotFoundMessage(): string
    {
        return 'アカウントが登録されていません。アカウント作成ページよりアカウントを作成してください。';
    }
}
