<?php
/**
 * AccountScenario
 */

namespace App\Services;

use Ramsey\Uuid\Uuid;
use App\Models\Domain\QiitaAccountValue;
use App\Models\Domain\Account\AccountEntity;
use App\Models\Domain\QiitaAccountValueBuilder;
use App\Models\Domain\Account\AccountRepository;
use App\Models\Domain\Category\CategoryRepository;
use App\Models\Domain\Account\AccountSpecification;
use App\Models\Domain\Exceptions\ValidationException;
use App\Models\Domain\LoginSession\LoginSessionEntity;
use App\Models\Domain\Exceptions\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Domain\Exceptions\AccountCreatedException;
use App\Models\Domain\LoginSession\LoginSessionRepository;
use App\Models\Domain\LoginSession\LoginSessionEntityBuilder;
use App\Models\Domain\Exceptions\LoginSessionExpiredException;

/**
 * Class AccountScenario
 * @package App\Services
 */
class AccountScenario
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
     * CategoryRepository
     *
     * @var
     */
    private $categoryRepository;

    /**
     * AccountScenario constructor.
     * @param AccountRepository $accountRepository
     * @param LoginSessionRepository $loginSessionRepository
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        AccountRepository $accountRepository,
        LoginSessionRepository $loginSessionRepository,
        CategoryRepository $categoryRepository
    ) {
        $this->accountRepository = $accountRepository;
        $this->loginSessionRepository = $loginSessionRepository;
        $this->categoryRepository = $categoryRepository;
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
            $errors = AccountSpecification::canCreateQiitaAccountValue($requestArray);
            if ($errors) {
                throw new ValidationException(QiitaAccountValue::createAccountValidationErrorMessage(), $errors);
            }

            $qiitaAccountValueBuilder = new QiitaAccountValueBuilder();
            $qiitaAccountValueBuilder->setAccessToken($requestArray['accessToken']);
            $qiitaAccountValueBuilder->setPermanentId($requestArray['permanentId']);
            $qiitaAccountValueBuilder->setUserName($requestArray['qiitaAccountId']);
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
            $accountEntity = $this->findAccountEntity($params, $this->loginSessionRepository, $this->accountRepository);

            \DB::beginTransaction();

            $accountEntity->cancel($this->accountRepository, $this->loginSessionRepository, $this->categoryRepository);

            \DB::commit();
        } catch (ModelNotFoundException $e) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
        } catch (\PDOException $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
