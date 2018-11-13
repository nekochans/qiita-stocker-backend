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
use App\Models\Domain\AccountSpecification;
use App\Models\Domain\LoginSessionRepository;
use App\Models\Domain\QiitaAccountValueBuilder;
use App\Models\Domain\LoginSessionEntityBuilder;
use App\Models\Domain\Exceptions\ValidationException;
use App\Models\Domain\Exceptions\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Domain\Exceptions\AccountCreatedException;
use App\Models\Domain\Exceptions\LoginSessionExpiredException;

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
        try {
            $errors = AccountSpecification::canCreate($requestArray);
            if ($errors) {
                throw new ValidationException(QiitaAccountValue::createAccountValidationErrorMessage(), $errors);
            }

            $qiitaAccountValueBuilder = new QiitaAccountValueBuilder();
            $qiitaAccountValueBuilder->setAccessToken($requestArray['accessToken']);
            $qiitaAccountValueBuilder->setPermanentId($requestArray['permanentId']);
            $qiitaAccountValue = $qiitaAccountValueBuilder->build();

            if ($qiitaAccountValue->isCreatedAccount($this->accountRepository)) {
                throw new AccountCreatedException(AccountEntity::accountCreatedMessage());
            }

            \DB::beginTransaction();

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

            \DB::commit();
        } catch (\PDOException $e) {
            \DB::rollBack();
            throw $e;
        }

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
        try {
            if ($params['sessionId'] === null) {
                throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
            }

            $loginSessionEntity = $this->loginSessionRepository->find($params['sessionId']);

            if ($loginSessionEntity->isExpired()) {
                throw new LoginSessionExpiredException($loginSessionEntity->loginSessionExpiredMessage());
            }

            $accountEntity = $loginSessionEntity->findHasAccountEntity($this->accountRepository);

            \DB::beginTransaction();

            $accountEntity->cancel($this->accountRepository, $this->loginSessionRepository);

            \DB::commit();
        } catch (ModelNotFoundException $e) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
        } catch (\PDOException $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
