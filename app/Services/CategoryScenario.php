<?php
/**
 * CategoryScenario
 */

namespace App\Services;

use App\Models\Domain\QiitaApiRepository;
use GuzzleHttp\Exception\RequestException;
use App\Models\Domain\Category\CategoryEntity;
use App\Models\Domain\Account\AccountRepository;
use App\Models\Domain\Category\CategoryNameValue;
use App\Models\Domain\Category\CategoryRepository;
use App\Models\Domain\Category\CategoryStockEntity;
use App\Models\Domain\Category\CategoryEntityBuilder;
use App\Models\Domain\Category\CategorySpecification;
use App\Models\Domain\Exceptions\ValidationException;
use App\Models\Domain\LoginSession\LoginSessionEntity;
use App\Models\Domain\Exceptions\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Domain\LoginSession\LoginSessionRepository;
use App\Models\Domain\Exceptions\CategoryNotFoundException;
use App\Models\Domain\Exceptions\ServiceUnavailableException;
use App\Models\Domain\exceptions\LoginSessionExpiredException;
use App\Models\Domain\Exceptions\CategoryRelationNotFoundException;

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
     * QiitaApiRepository
     *
     * @var
     */
    private $qiitaApiRepository;

    /**
     * CategoryScenario constructor.
     * @param AccountRepository $accountRepository
     * @param LoginSessionRepository $loginSessionRepository
     * @param CategoryRepository $categoryRepository
     * @param QiitaApiRepository $qiitaApiRepository
     */
    public function __construct(
        AccountRepository $accountRepository,
        LoginSessionRepository $loginSessionRepository,
        CategoryRepository $categoryRepository,
        QiitaApiRepository $qiitaApiRepository
    ) {
        $this->accountRepository = $accountRepository;
        $this->loginSessionRepository = $loginSessionRepository;
        $this->categoryRepository = $categoryRepository;
        $this->qiitaApiRepository = $qiitaApiRepository;
    }

    /**
     * カテゴリを作成する
     *
     * @param array $params
     * @return array
     * @throws LoginSessionExpiredException
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public function create(array $params): array
    {
        try {
            $errors = CategorySpecification::canCreateCategoryNameValue($params);
            if ($errors) {
                throw new ValidationException(CategoryNameValue::nameValidationErrorMessage(), $errors);
            }

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

        $category = [
            'categoryId'   => $categoryEntity->getId(),
            'name'         => $categoryEntity->getCategoryNameValue()->getName()
        ];

        return $category;
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

    /**
     * 指定されたカテゴリを更新する
     *
     * @param array $params
     * @return array
     * @throws CategoryNotFoundException
     * @throws LoginSessionExpiredException
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public function update(array $params): array
    {
        try {
            $accountEntity = $this->findAccountEntity($params, $this->loginSessionRepository, $this->accountRepository);
        } catch (ModelNotFoundException $e) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
        } catch (\PDOException $e) {
            throw $e;
        }

        try {
            $errors = CategorySpecification::canSetCategoryEntityId($params);
            if ($errors) {
                throw new ValidationException(CategoryEntity::categoryIdValidationErrorMessage(), $errors);
            }

            $errors = CategorySpecification::canCreateCategoryNameValue($params);
            if ($errors) {
                throw new ValidationException(CategoryNameValue::nameValidationErrorMessage(), $errors);
            }

            $accountEntity->findHasCategoryEntity($this->categoryRepository, $params['id']);

            \DB::beginTransaction();

            $categoryNameValue = new CategoryNameValue($params['name']);
            $categoryEntityBuilder = new CategoryEntityBuilder();
            $categoryEntityBuilder->setId($params['id']);
            $categoryEntityBuilder->setCategoryNameValue($categoryNameValue);
            $categoryEntity = $categoryEntityBuilder->build();

            $this->categoryRepository->updateName($categoryEntity);

            \DB::commit();
        } catch (ModelNotFoundException $e) {
            throw new CategoryNotFoundException(CategoryEntity::categoryNotFoundMessage());
        } catch (\PDOException $e) {
            \DB::rollBack();
            throw $e;
        }

        $category = [
            'categoryId'   => $categoryEntity->getId(),
            'name'         => $categoryEntity->getCategoryNameValue()->getName()
        ];

        return $category;
    }

    /**
     * カテゴリを削除する
     *
     * @param array $params
     * @throws CategoryNotFoundException
     * @throws LoginSessionExpiredException
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public function destroy(array $params)
    {
        try {
            $accountEntity = $this->findAccountEntity($params, $this->loginSessionRepository, $this->accountRepository);
        } catch (ModelNotFoundException $e) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
        } catch (\PDOException $e) {
            throw $e;
        }

        try {
            $errors = CategorySpecification::canSetCategoryEntityId($params);
            if ($errors) {
                throw new ValidationException(CategoryEntity::categoryIdValidationErrorMessage(), $errors);
            }

            $categoryEntity = $accountEntity->findHasCategoryEntity($this->categoryRepository, $params['id']);

            \DB::beginTransaction();

            $this->categoryRepository->destroy($categoryEntity);

            \DB::commit();
        } catch (ModelNotFoundException $e) {
            throw new CategoryNotFoundException(CategoryEntity::categoryNotFoundMessage());
        } catch (\PDOException $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * カテゴリとストックのリレーションを作成する
     *
     * @param array $params
     * @throws CategoryNotFoundException
     * @throws LoginSessionExpiredException
     * @throws ServiceUnavailableException
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public function categorize(array $params)
    {
        try {
            $accountEntity = $this->findAccountEntity($params, $this->loginSessionRepository, $this->accountRepository);
        } catch (ModelNotFoundException $e) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
        } catch (\PDOException $e) {
            throw $e;
        }

        try {
            $errors = CategorySpecification::canSetCategoryEntityId($params);
            if ($errors) {
                throw new ValidationException(CategoryEntity::categoryIdValidationErrorMessage(), $errors);
            }

            $errors = CategorySpecification::canCreateCategoriesStocks($params);
            if ($errors) {
                throw new ValidationException(CategoryEntity::createCategoriesStocksValidationErrorMessage(), $errors);
            }

            $categoryEntity = $accountEntity->findHasCategoryEntity($this->categoryRepository, $params['id']);

            \DB::beginTransaction();

            $categoryEntity->categorize($this->categoryRepository, $this->qiitaApiRepository, $accountEntity, $params['articleIds']);

            \DB::commit();
        } catch (ModelNotFoundException $e) {
            throw new CategoryNotFoundException(CategoryEntity::categoryNotFoundMessage());
        } catch (RequestException $e) {
            \DB::rollBack();
            throw new ServiceUnavailableException();
        } catch (\PDOException $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * カテゴリとストックのリレーションを削除する
     *
     * @param array $params
     * @throws CategoryRelationNotFoundException
     * @throws LoginSessionExpiredException
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public function destroyRelation(array $params)
    {
        try {
            $accountEntity = $this->findAccountEntity($params, $this->loginSessionRepository, $this->accountRepository);
        } catch (ModelNotFoundException $e) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
        } catch (\PDOException $e) {
            throw $e;
        }

        try {
            $errors = CategorySpecification::canDestroyRelation($params);
            if ($errors) {
                throw new ValidationException(CategoryStockEntity::idValidationErrorMessage(), $errors);
            }

            $categoryStockEntity = $accountEntity->findHasCategoryStockEntity($this->categoryRepository, $params['id']);

            \DB::beginTransaction();

            $this->categoryRepository->destroyCategoriesStocks([$categoryStockEntity->getId()]);

            \DB::commit();
        } catch (ModelNotFoundException $e) {
            throw new CategoryRelationNotFoundException(CategoryStockEntity::categoryStockNotFoundMessage());
        } catch (\PDOException $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
