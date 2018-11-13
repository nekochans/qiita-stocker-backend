<?php
/**
 * CategoryScenario
 */

namespace App\Services;

use App\Models\Domain\AccountRepository;
use App\Models\Domain\LoginSessionEntity;
use App\Models\Domain\LoginSessionRepository;
use App\Models\Domain\Category\CategoryNameValue;
use App\Models\Domain\Category\CategoryRepository;
use App\Models\Domain\exceptions\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Domain\exceptions\LoginSessionExpiredException;

class CategoryScenario
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
     * LoginSessionRepository
     *
     * @var
     */
    private $categoryRepository;

    /**
     * CategoryScenario constructor.
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
     * カテゴリを作成する
     *
     * @param array $params
     * @return array
     * @throws LoginSessionExpiredException
     * @throws UnauthorizedException
     */
    public function create(array $params): array
    {
        try {
            // TODO バリデーションを追加する

            if ($params['sessionId'] === null) {
                throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
            }

            $loginSessionEntity = $this->loginSessionRepository->find($params['sessionId']);

            if ($loginSessionEntity->isExpired()) {
                throw new LoginSessionExpiredException($loginSessionEntity->loginSessionExpiredMessage());
            }

            $accountEntity = $loginSessionEntity->findHasAccountEntity($this->accountRepository);

            \DB::beginTransaction();

            $categoryNameValue = new CategoryNameValue($params['name']);

            $categoryEntity = $this->categoryRepository->create($accountEntity, $categoryNameValue);

            \DB::commit();
        } catch (ModelNotFoundException $e) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
        } catch (\PDOException $e) {
            \DB::rollBack();
            throw $e;
        }

        $categories = [
            'categoryId'   => $categoryEntity->getId(),
            'name'         => $categoryEntity->getCategoryNameValue()->getName()
        ];

        return $categories;
    }
}
