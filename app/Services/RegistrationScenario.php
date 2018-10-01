<?php
/**
 * RegistrationScenario
 */

namespace App\Services;

use Ramsey\Uuid\Uuid;
use App\Models\Domain\QiitaAccountValueBuilder;

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
        $builder = new QiitaAccountValueBuilder();
        $builder->setAccessToken($requestObject['accessToken']);
        $builder->setPermanentId($requestObject['permanentId']);
        $qiitaAccountValue = $builder->build();

        // TODO QiitaAccountValueを用いて、ユーザをDBに登録する

        $sessionId = Uuid::uuid4();
        $responseArray = ['$sessionId' => $sessionId];

        // TODO LoginSessionをDBに保存すする

        return $responseArray;
    }
}
