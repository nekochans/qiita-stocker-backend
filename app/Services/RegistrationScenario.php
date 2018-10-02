<?php
/**
 * RegistrationScenario
 */

namespace App\Services;

use Ramsey\Uuid\Uuid;
use App\Models\Domain\QiitaAccountValueBuilder;
use App\Models\Domain\LoginSessionEntityBuilder;
use App\Infrastructure\Repositories\RegistrationRepository;

/**
 * Class RegistrationScenario
 * @package App\Services
 */
class RegistrationScenario
{
    /**
     * ユーザを登録する
     *
     * @param array $requestObject
     * @return array
     * @throws \Exception
     */
    public function registration(array $requestObject): array
    {
        $registrationRepository = new RegistrationRepository();

        $qiitaAccountValueBuilder = new QiitaAccountValueBuilder();
        $qiitaAccountValueBuilder->setAccessToken($requestObject['accessToken']);
        $qiitaAccountValueBuilder->setPermanentId($requestObject['permanentId']);
        $qiitaAccountValue = $qiitaAccountValueBuilder->build();

        $accountEntity = $registrationRepository->createAccount($qiitaAccountValue);

        $sessionId = Uuid::uuid4();
        $loginSessionEntityBuilder = new LoginSessionEntityBuilder();
        $loginSessionEntityBuilder->setAccountId($accountEntity->getAccountId());
        $loginSessionEntityBuilder->setSessionId($sessionId);
        $loginSessionEntity = $loginSessionEntityBuilder->build();

        $registrationRepository->saveLoginSession($loginSessionEntity);

        $responseArray = [
            'accountId' => $loginSessionEntity->getAccountId(),
            '_embedded' => ['sessionId' => $loginSessionEntity->getSessionId()]
            ];

        return $responseArray;
    }
}
