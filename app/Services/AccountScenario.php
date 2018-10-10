<?php
/**
 * AccountScenario
 */

namespace App\Services;

use Ramsey\Uuid\Uuid;
use App\Models\Domain\AccountRepository;
use App\Models\Domain\QiitaAccountValueBuilder;
use App\Models\Domain\LoginSessionEntityBuilder;

/**
 * Class AccountScenario
 * @package App\Services
 */
class AccountScenario
{

    /**
     * AccountRepository
     *
     * @var
     */
    private $accountRepository;

    /**
     * AccountScenario constructor.
     * @param AccountRepository $accountRepository
     */
    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * アカウントを作成する
     *
     * @param array $requestArray
     * @return array
     * @throws \Exception
     */
    public function create(array $requestArray): array
    {
        $qiitaAccountValueBuilder = new QiitaAccountValueBuilder();
        $qiitaAccountValueBuilder->setAccessToken($requestArray['accessToken']);
        $qiitaAccountValueBuilder->setPermanentId($requestArray['permanentId']);
        $qiitaAccountValue = $qiitaAccountValueBuilder->build();

        $accountEntity = $qiitaAccountValue->findAccountEntityByPermanentId($this->accountRepository);

        if ($accountEntity) {
            $accountEntity->updateAccessToken($this->accountRepository);
        } else {
            $accountEntity = $this->accountRepository->create($qiitaAccountValue);
        }

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
            'accountId' => $loginSessionEntity->getAccountId(),
            '_embedded' => ['sessionId' => $loginSessionEntity->getSessionId()]
        ];

        return $responseArray;
    }
}
