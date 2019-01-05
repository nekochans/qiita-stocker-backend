<?php
/**
 * LoginSessionScenario
 */

namespace App\Services;

use Ramsey\Uuid\Uuid;
use App\Models\Domain\QiitaAccountValue;
use App\Models\Domain\Account\AccountEntity;
use App\Models\Domain\QiitaAccountValueBuilder;
use App\Models\Domain\Account\AccountRepository;
use App\Models\Domain\Exceptions\ValidationException;
use App\Models\Domain\LoginSession\LoginSessionEntity;
use App\Models\Domain\Exceptions\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Domain\Exceptions\AccountNotFoundException;
use App\Models\Domain\LoginSession\LoginSessionRepository;
use App\Models\Domain\LoginSession\LoginSessionEntityBuilder;
use App\Models\Domain\LoginSession\LoginSessionSpecification;

/**
 * Class LoginSessionScenario
 * @package App\Services
 */
class LoginSessionScenario
{
    use Authentication;

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
     * LoginSessionScenario constructor.
     * @param AccountRepository $accountRepository
     * @param LoginSessionRepository $loginSessionRepository
     */
    public function __construct(AccountRepository $accountRepository, LoginSessionRepository $loginSessionRepository)
    {
        $this->accountRepository = $accountRepository;
        $this->loginSessionRepository = $loginSessionRepository;
    }

    /**
     * ログインセッションを作成する
     *
     * @param array $requestArray
     * @return array
     * @throws AccountNotFoundException
     * @throws ValidationException
     */
    public function create(array $requestArray): array
    {
        try {
            $errors = LoginSessionSpecification::canCreateQiitaAccountValue($requestArray);
            if ($errors) {
                throw new ValidationException(QiitaAccountValue::createLoginSessionValidationErrorMessage(), $errors);
            }

            $qiitaAccountValueBuilder = new QiitaAccountValueBuilder();
            $qiitaAccountValueBuilder->setAccessToken($requestArray['accessToken']);
            $qiitaAccountValueBuilder->setUserName($requestArray['qiitaAccountId']);
            $qiitaAccountValueBuilder->setPermanentId($requestArray['permanentId']);
            $qiitaAccountValue = $qiitaAccountValueBuilder->build();

            if (!$qiitaAccountValue->isCreatedAccount($this->accountRepository)) {
                throw new AccountNotFoundException(AccountEntity::accountNotFoundMessage());
            }

            $accountEntity = $qiitaAccountValue->findAccountEntityByPermanentId($this->accountRepository);

            \DB::beginTransaction();

            $accountEntity = $accountEntity->updateAccessToken($this->accountRepository, $qiitaAccountValue);

            if ($accountEntity->isChangedQiitaUserName($qiitaAccountValue)) {
                $accountEntity = $accountEntity->updateQiitaUserName($this->accountRepository, $qiitaAccountValue);
            }

            $sessionId = Uuid::uuid4();
            $expiredOn = LoginSessionSpecification::loginSessionExpiration();

            $loginSessionEntityBuilder = new LoginSessionEntityBuilder();
            $loginSessionEntityBuilder->setAccountId($accountEntity->getAccountId());
            $loginSessionEntityBuilder->setSessionId($sessionId);
            $loginSessionEntityBuilder->setExpiredOn($expiredOn);
            $loginSessionEntity = $loginSessionEntityBuilder->build();

            $this->loginSessionRepository->save($loginSessionEntity);

            \DB::commit();
        } catch (\PDOException $e) {
            \DB::rollBack();
            throw $e;
        }

        $responseArray = [
            'sessionId' => $loginSessionEntity->getSessionId()
        ];

        return $responseArray;
    }

    /**
     * ログインセッションを削除する
     *
     * @param array $params
     * @throws UnauthorizedException
     * @throws \App\Models\Domain\Exceptions\LoginSessionExpiredException
     */
    public function destroy(array $params)
    {
        try {
            $accountEntity = $this->findAccountEntity($params, $this->loginSessionRepository, $this->accountRepository);

            \DB::beginTransaction();
            $this->loginSessionRepository->destroy($params['sessionId']);
            \DB::commit();
        } catch (ModelNotFoundException $e) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
        } catch (\PDOException $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
