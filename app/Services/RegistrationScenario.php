<?php
/**
 * RegistrationScenario
 */

namespace App\Services;

use Ramsey\Uuid\Uuid;
use App\Models\Domain\AccountEntityBuilder;
use App\Models\Domain\QiitaAccountValueBuilder;
use App\Models\Domain\LoginSessionEntityBuilder;

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
        $qiitaAccountValueBuilder = new QiitaAccountValueBuilder();
        $qiitaAccountValueBuilder->setAccessToken($requestObject['accessToken']);
        $qiitaAccountValueBuilder->setPermanentId($requestObject['permanentId']);
        $qiitaAccountValue = $qiitaAccountValueBuilder->build();

        $accountId = '1'; // 登録後に発行されたアカウントIDを指定する

        // AccountEntity を作成
        // TODO accountをDBに登録する

        $accountEntityBuilder = new AccountEntityBuilder();
        $accountEntityBuilder->setAccountId($accountId);
        $accountEntityBuilder->setPermanentId($qiitaAccountValue->getPermanentId());
        $accountEntityBuilder->setAccessToken($qiitaAccountValue->getAccessToken());
        $accountEntity = $accountEntityBuilder->build();

        $sessionId = Uuid::uuid4();

        // LoginSessionEntity を作成
        $loginSessionEntityBuilder = new LoginSessionEntityBuilder();
        $loginSessionEntityBuilder->setAccountId($accountEntity->getAccountId());
        $loginSessionEntityBuilder->setSessionId($sessionId);
        $loginSessionEntity = $loginSessionEntityBuilder->build();

        $responseArray = [
            'accountId' => $loginSessionEntity->getAccountId(),
            '_embedded' => ['sessionId' => $loginSessionEntity->getSessionId()]
            ];

        // TODO LoginSessionをDBに保存すする

        return $responseArray;
    }
}
