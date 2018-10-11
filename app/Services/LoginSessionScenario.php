<?php
/**
 * LoginScenario
 */

namespace App\Services;

use Ramsey\Uuid\Uuid;
use App\Models\Domain\AccountRepository;
use App\Models\Domain\QiitaAccountValueBuilder;
use App\Models\Domain\LoginSessionEntityBuilder;

/**
 * Class LoginScenario
 * @package App\Services
 */
class LoginScenario
{
    /**
     * AccountRepository
     *
     * @var
     */
    private $accountRepository;

    /**
     * LoginScenario constructor.
     * @param AccountRepository $accountRepository
     */
    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * ログインセッションを発行する
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
            $accountEntity = $accountEntity->updateAccessToken($this->accountRepository, $qiitaAccountValue);
        } else {
            // TODO アカウントが作成されていない場合、エラーを返す
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
            'sessionId' => $loginSessionEntity->getSessionId()
        ];

        return $responseArray;
    }
}
