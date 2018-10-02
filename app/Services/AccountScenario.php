<?php
/**
 * AccountScenario
 */

namespace App\Services;

use Ramsey\Uuid\Uuid;
use App\Models\Domain\QiitaAccountValueBuilder;
use App\Models\Domain\LoginSessionEntityBuilder;
use App\Infrastructure\Repositories\RegistrationRepository;

/**
 * Class AccountScenario
 * @package App\Services
 */
class AccountScenario
{
    /**
     * アカウントを作成する
     *
     * @param array $requestObject
     * @return array
     * @throws \Exception
     */
    public function create(array $requestObject): array
    {
        $registrationRepository = new RegistrationRepository();

        $qiitaAccountValueBuilder = new QiitaAccountValueBuilder();
        $qiitaAccountValueBuilder->setAccessToken($requestObject['accessToken']);
        $qiitaAccountValueBuilder->setPermanentId($requestObject['permanentId']);
        $qiitaAccountValue = $qiitaAccountValueBuilder->build();

        $accountEntity = $registrationRepository->createAccount($qiitaAccountValue);

        $sessionId = Uuid::uuid4();

        // TODO 有効期限を適切な期限に修正
        $expiredOn = new \DateTime();
        $expiredOn->add(new \DateInterval('PT1H'));

        $loginSessionEntityBuilder = new LoginSessionEntityBuilder();
        $loginSessionEntityBuilder->setAccountId($accountEntity->getAccountId());
        $loginSessionEntityBuilder->setSessionId($sessionId);
        $loginSessionEntityBuilder->setExpiredOn($expiredOn);
        $loginSessionEntity = $loginSessionEntityBuilder->build();

        $registrationRepository->saveLoginSession($loginSessionEntity);

        $responseArray = [
            'accountId' => $loginSessionEntity->getAccountId(),
            '_embedded' => ['sessionId' => $loginSessionEntity->getSessionId()]
        ];

        return $responseArray;
    }
}
