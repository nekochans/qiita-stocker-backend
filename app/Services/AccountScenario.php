<?php
/**
 * AccountScenario
 */

namespace App\Services;

use Ramsey\Uuid\Uuid;
use App\Models\Domain\AccountEntity;
use App\Models\Domain\AccountRepository;
use App\Models\Domain\QiitaAccountValue;
use App\Models\Domain\LoginSessionEntity;
use App\Models\Domain\LoginSessionRepository;
use App\Models\Domain\QiitaAccountValueBuilder;
use App\Models\Domain\LoginSessionEntityBuilder;
use App\Models\Domain\exceptions\ValidationException;
use App\Models\Domain\exceptions\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Domain\exceptions\AccountCreatedException;
use App\Models\Domain\exceptions\LoginSessionExpiredException;

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
     * @throws LoginSessionExpiredException
     * @throws UnauthorizedException
     */
    public function destroy(array $params)
    {
        if ($params['sessionId'] === null) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
        }

        try {
            $loginSessionEntity = $this->loginSessionRepository->find($params['sessionId']);

            if ($loginSessionEntity->isExpired()) {
                throw new LoginSessionExpiredException($loginSessionEntity->loginSessionExpiredMessage());
            }

            $accountEntity = $loginSessionEntity->findHasAccountEntity($this->accountRepository);
            $accountEntity->cancel($this->accountRepository, $this->loginSessionRepository);
        } catch (ModelNotFoundException $e) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
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
            'accessToken' => 'required|regex:/^[a-z0-9]+$/|min:40|max:64',
            'permanentId' => 'required|integer|min:1|max:4294967294',
        ]);

        if ($validator->fails()) {
            throw new ValidationException(QiitaAccountValue::createAccountValidationErrorMessage(), $validator->errors()->toArray());
        }
    }
}
