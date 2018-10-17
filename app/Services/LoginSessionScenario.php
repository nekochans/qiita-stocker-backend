<?php
/**
 * LoginSessionScenario
 */

namespace App\Services;

use Ramsey\Uuid\Uuid;
use App\Models\Domain\AccountEntity;
use App\Models\Domain\AccountRepository;
use App\Models\Domain\QiitaAccountValueBuilder;
use App\Models\Domain\LoginSessionEntityBuilder;
use App\Models\Domain\exceptions\AccountNotFoundException;

/**
 * Class LoginSessionScenario
 * @package App\Services
 */
class LoginSessionScenario
{
    /**
     * AccountRepository
     *
     * @var
     */
    private $accountRepository;

    /**
     * LoginSessionScenario constructor.
     * @param AccountRepository $accountRepository
     */
    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * ログインセッションを作成する
     *
     * @param array $requestArray
     * @return array
     * @throws AccountNotFoundException
     */
    public function create(array $requestArray): array
    {
        $qiitaAccountValueBuilder = new QiitaAccountValueBuilder();
        $qiitaAccountValueBuilder->setAccessToken($requestArray['accessToken']);
        $qiitaAccountValueBuilder->setPermanentId($requestArray['permanentId']);
        $qiitaAccountValue = $qiitaAccountValueBuilder->build();

        if (!$qiitaAccountValue->isCreatedAccount($this->accountRepository)) {
            throw new AccountNotFoundException(AccountEntity::accountNotFoundMessage());
        }

        $accountEntity = $qiitaAccountValue->findAccountEntityByPermanentId($this->accountRepository);

        $accountEntity = $accountEntity->updateAccessToken($this->accountRepository, $qiitaAccountValue);
        $sessionId = Uuid::uuid4();

        // TODO 有効期限を適切な期限に修正
        $expiredOn = new \DateTime();
        $expiredOn->add(new \DateInterval('PT1H'));

        $loginSessionEntityBuilder = new LoginSessionEntityBuilder();
        $loginSessionEntityBuilder->setAccountId($accountEntity->getAccountId());
        $loginSessionEntityBuilder->setSessionId($sessionId);
        $loginSessionEntityBuilder->setExpiredOn($expiredOn);
        $loginSessionEntity = $loginSessionEntityBuilder->build();

        $this->accountRepository->saveLoginSession($loginSessionEntity);

        $responseArray = [
            'sessionId' => $loginSessionEntity->getSessionId()
        ];

        return $responseArray;
    }
}
