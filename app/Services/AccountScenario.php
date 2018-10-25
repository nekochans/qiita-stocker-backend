<?php
/**
 * AccountScenario
 */

namespace App\Services;

use Ramsey\Uuid\Uuid;
use App\Models\Domain\AccountEntity;
use App\Models\Domain\AccountRepository;
use App\Models\Domain\LoginSessionRepository;
use App\Models\Domain\QiitaAccountValueBuilder;
use App\Models\Domain\LoginSessionEntityBuilder;
use App\Models\Domain\exceptions\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Domain\exceptions\AccountCreatedException;

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
     * LoginSessionRepository
     *
     * @var
     */
    private $loginSessionRepository;
    /**
     * AccountScenario constructor.
     * @param AccountRepository $accountRepository
     * @param LoginSessionRepository $loginSessionRepository
     */
    public function __construct(AccountRepository $accountRepository, LoginSessionRepository $loginSessionRepository)
    {
        $this->accountRepository = $accountRepository;
        $this->loginSessionRepository = $loginSessionRepository;
    }

    /**
     * アカウントを作成する
     *
     * @param array $requestArray
     * @return array
     * @throws AccountCreatedException
     * @throws ValidationException
     */
    public function create(array $requestArray): array
    {
        $this->validateAccountValue($requestArray);

        $qiitaAccountValueBuilder = new QiitaAccountValueBuilder();
        $qiitaAccountValueBuilder->setAccessToken($requestArray['accessToken']);
        $qiitaAccountValueBuilder->setPermanentId($requestArray['permanentId']);
        $qiitaAccountValue = $qiitaAccountValueBuilder->build();

        if ($qiitaAccountValue->isCreatedAccount($this->accountRepository)) {
            throw new AccountCreatedException(AccountEntity::accountCreatedMessage());
        }

        $accountEntity = $this->accountRepository->create($qiitaAccountValue);

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

    /**
     * アカウントを削除する
     *
     * @param array $params
     */
    public function destroy(array $params)
    {
        try {
            $loginSessionEntity = $this->loginSessionRepository->find($params['sessionId']);

            // TODO ログインセッションの有効期限の検証を行う

            $accountEntity = $loginSessionEntity->findHasAccountEntity($this->accountRepository);
            $accountEntity->cancel($this->accountRepository, $this->loginSessionRepository);
        } catch (ModelNotFoundException $e) {
            // TODO LoginSessionEntity、AccountEntityが存在しなかった場合のエラー処理を追加する
        }
    }

    /**
     * バリデーションを行う
     *
     * @param array $requestArray
     * @throws ValidationException
     */
    private function validateAccountValue(array $requestArray)
    {
        $validator = \Validator::make($requestArray, [
            'accessToken' => 'required|alpha_num|min:40|max:64',
            'permanentId' => 'required|integer|min:1|max:4294967294',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors()->toArray());
        }
    }
}
