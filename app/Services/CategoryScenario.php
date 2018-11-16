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

            $accountEntity = $this->findAccountEntity($params, $this->loginSessionRepository, $this->accountRepository);

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

    /**
     * カテゴリ一覧を取得する
     *
     * @param array $params
     * @return array
     * @throws LoginSessionExpiredException
     * @throws UnauthorizedException
     */
    public function index(array $params): array
    {
        try {
            $accountEntity = $this->findAccountEntity($params, $this->loginSessionRepository, $this->accountRepository);

            $categoryEntities = $this->categoryRepository->search($accountEntity);
        } catch (ModelNotFoundException $e) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
        } catch (\PDOException $e) {
            throw $e;
        }

        $categories = [];
        $categoryEntityList = $categoryEntities->getCategoryEntities();

        foreach ($categoryEntityList as $categoryEntity) {
            $category = [
                'categoryId'   => $categoryEntity->getId(),
                'name'         => $categoryEntity->getCategoryNameValue()->getName()
            ];
            array_push($categories, $category);
        }
        return $categories;
    }
}
