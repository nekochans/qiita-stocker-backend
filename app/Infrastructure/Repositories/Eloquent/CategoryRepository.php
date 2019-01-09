<?php
/**
 * CategoryRepository
 */

namespace App\Infrastructure\Repositories\Eloquent;

use App\Eloquents\Category;
use App\Eloquents\CategoryName;
use App\Eloquents\CategoryStock;
use App\Models\Domain\Stock\StockValues;
use App\Models\Domain\Account\AccountEntity;
use App\Models\Domain\Category\CategoryEntity;
use App\Models\Domain\Stock\StockValueBuilder;
use App\Models\Domain\Category\CategoryEntities;
use App\Models\Domain\Category\CategoryNameValue;
use App\Models\Domain\Category\CategoryStockEntity;
use App\Models\Domain\Category\CategoryEntityBuilder;
use App\Models\Domain\Category\CategoryStockEntities;
use App\Models\Domain\Category\CategoryStockEntityBuilder;

/**
 * Class CategoryRepository
 * @package App\Infrastructure\Repositories\Eloquent
 */
class CategoryRepository implements \App\Models\Domain\Category\CategoryRepository
{
    /**
     * カテゴリを作成する
     *
     * @param AccountEntity $accountEntity
     * @param CategoryNameValue $categoryNameValue
     * @return CategoryEntity
     */    public function create(AccountEntity $accountEntity, CategoryNameValue $categoryNameValue): CategoryEntity
    {
        $categoryId = $this->saveCategories($accountEntity->getAccountId());
        $this->saveCategoriesNames($categoryId, $categoryNameValue);

        $categoryEntityBuilder = new CategoryEntityBuilder();
        $categoryEntityBuilder->setId($categoryId);
        $categoryEntityBuilder->setCategoryNameValue($categoryNameValue);
        return $categoryEntityBuilder->build();
    }

    /**
     * categories テーブルにデータを保存する
     *
     * @param int $accountId
     * @return int
     */
    private function saveCategories(int $accountId): int
    {
        $category = new Category();
        $category->account_id = $accountId;
        $category->save();
        $categoryId = $category->getAttribute('id');

        return $categoryId;
    }

    /**
     * categories_names テーブルにデータを保存する
     *
     * @param int $categoryId
     * @param CategoryNameValue $categoryNameValue
     */
    private function saveCategoriesNames(int $categoryId, CategoryNameValue $categoryNameValue)
    {
        $categoryName = new CategoryName();
        $categoryName->category_id = $categoryId;
        $categoryName->name = $categoryNameValue->getName();
        $categoryName->save();
    }

    /**
     * カテゴリ一覧を取得する
     *
     * @param AccountEntity $accountEntity
     * @return CategoryEntities
     */
    public function search(AccountEntity $accountEntity): CategoryEntities
    {
        $categories = Category::select('categories.id', 'categories_names.name')
            ->where('account_id', $accountEntity->getAccountId())
            ->join('categories_names', 'categories.id', '=', 'categories_names.category_id')
            ->get();

        $categoryEntityCollection = $categories->map(function (Category $category): CategoryEntity {
            return $this->buildCategoryEntity($category->toArray());
        });

        $categoryEntities = new CategoryEntities(...$categoryEntityCollection->toArray());

        return $categoryEntities;
    }

    /**
     * CategoryEntityを作成する
     *
     * @param array $category
     * @return CategoryEntity
     */
    private function buildCategoryEntity(array $category): CategoryEntity
    {
        $categoryNameVale = new CategoryNameValue($category['name']);
        $categoryEntityBuilder = new CategoryEntityBuilder();
        $categoryEntityBuilder->setId($category['id']);
        $categoryEntityBuilder->setCategoryNameValue($categoryNameVale);

        return $categoryEntityBuilder->build();
    }

    /**
     * アカウントに紐づくカテゴリを全て削除する
     *
     * @param string $accountId
     */
    public function destroyAll(string $accountId)
    {
        $categories = Category::where('account_id', $accountId);

        $categoryIdList = $categories->get()->pluck('id');
        CategoryName::whereIn('category_id', $categoryIdList)->delete();
        CategoryStock::whereIn('category_id', $categoryIdList)->delete();

        $categories->delete();
    }

    /**
     * カテゴリを削除する
     * @param CategoryEntity $categoryEntity
     */
    public function destroy(CategoryEntity $categoryEntity)
    {
        CategoryName::where('category_id', $categoryEntity->getId())->delete();
        CategoryStock::where('category_id', $categoryEntity->getId())->delete();
        Category::destroy($categoryEntity->getId());
    }

    /**
     * カテゴリを取得する
     *
     * @param string $categoryId
     * @param string $accountId
     * @return CategoryEntity
     */
    public function findByIdAndAccountId(string $categoryId, string $accountId): CategoryEntity
    {
        $category = Category::where('account_id', $accountId)->where('id', $categoryId)->firstOrFail();
        $categoryName = CategoryName::where('category_Id', $category->id)->firstOrFail();

        $params = [
            'id'   => $category->id,
            'name' => $categoryName->name
        ];

        return $this->buildCategoryEntity($params);
    }

    /**
     * カテゴリ名を更新する
     *
     * @param CategoryEntity $categoryEntity
     */
    public function updateName(CategoryEntity $categoryEntity)
    {
        $categoryName = CategoryName::where('category_id', $categoryEntity->getId())->firstOrFail();

        $categoryName->name = $categoryEntity->getCategoryNameValue()->getName();

        $categoryName->save();
    }

    /**
     * カテゴリとストックのリレーションを作成する
     *
     * @param CategoryEntity $categoryEntity
     * @param StockValues $stockValues
     * @return mixed|void
     */
    public function createCategoriesStocks(CategoryEntity $categoryEntity, StockValues $stockValues)
    {
        $stockValueList = $stockValues->getStockValues();

        foreach ($stockValueList as $stockValue) {
            $tags = json_encode($stockValue->getTags());

            $categoryStock = new CategoryStock();
            $categoryStock->category_id = $categoryEntity->getId();
            $categoryStock->article_id = $stockValue->getArticleId();
            $categoryStock->title = $stockValue->getTitle();
            $categoryStock->user_id = $stockValue->getUserId();
            $categoryStock->profile_image_url = $stockValue->getProfileImageUrl();
            $categoryStock->article_created_at = $stockValue->getArticleCreatedAt();
            $categoryStock->tags = $tags;
            $categoryStock->save();
        }
    }

    /**
     * カテゴリとストックのリレーションを取得する
     *
     * @param CategoryEntity $categoryEntity
     * @param null $limit
     * @param int $offset
     * @return CategoryStockEntities
     */
    public function searchCategoriesStocksByCategoryId(CategoryEntity $categoryEntity, $limit = null, $offset = 0): CategoryStockEntities
    {
        if ($limit === null) {
            $categoryStocks = CategoryStock::where('category_id', $categoryEntity->getId())->get();
        } else {
            $categoryStocks = CategoryStock::where('category_id', $categoryEntity->getId())
                ->offset($offset)
                ->limit($limit)
                ->get();
        }

        $categoryStockEntityList = $categoryStocks->map(function (CategoryStock $categoryStock): CategoryStockEntity {
            return $this->buildCategoryStockEntity($categoryStock->toArray());
        });

        return new CategoryStockEntities(...$categoryStockEntityList->toArray());
    }

    /**
     * CategoryStockEntityを作成する
     *
     * @param array $categoryStock
     * @return CategoryStockEntity
     */
    private function buildCategoryStockEntity(array $categoryStock): CategoryStockEntity
    {
        $tags = json_decode($categoryStock['tags'], true);
        $articleCreatedAt = new \DateTime($categoryStock['article_created_at']);

        $stockValueBuilder = new StockValueBuilder();
        $stockValueBuilder->setArticleId($categoryStock['article_id']);
        $stockValueBuilder->setTitle($categoryStock['title']);
        $stockValueBuilder->setUserId($categoryStock['user_id']);
        $stockValueBuilder->setProfileImageUrl($categoryStock['profile_image_url']);
        $stockValueBuilder->setArticleCreatedAt($articleCreatedAt);
        $stockValueBuilder->setTags($tags);
        $stockValue = $stockValueBuilder->build();

        $categoryStockEntityBuilder = new CategoryStockEntityBuilder();
        $categoryStockEntityBuilder->setId($categoryStock['id']);
        $categoryStockEntityBuilder->setCategoryId($categoryStock['category_id']);
        $categoryStockEntityBuilder->setStockValue($stockValue);

        return $categoryStockEntityBuilder->build();
    }


    /**
     * 指定したカテゴリ以外にカテゴライズされているストックのArticleID一覧を取得する
     *
     * @param AccountEntity $accountEntity
     * @param CategoryEntity $categoryEntity
     * @param array $articleIdList
     * @return array
     */
    public function searchCategoriesStocksByArticleId(AccountEntity $accountEntity, CategoryEntity $categoryEntity, array $articleIdList): array
    {
        $categories = Category::select('categories_stocks.id')
            ->where('categories.account_id', $accountEntity->getAccountId())
            ->where('categories.id', '<>', $categoryEntity->getId())
            ->join('categories_stocks', function ($join) use ($articleIdList) {
                $join->on('categories.id', '=', 'categories_stocks.category_id')
                    ->whereIn('categories_stocks.article_id', $articleIdList);
            })
            ->get();

        $stockArticleIds = $categories->pluck('id');
        return $stockArticleIds->toArray();
    }

    /**
     * カテゴリとカテゴライズされているストックのArticleID一覧を取得する
     *
     * @param AccountEntity $accountEntity
     * @param array $articleIdList
     * @return array
     */
    public function searchCategoriesStocksAllByArticleId(AccountEntity $accountEntity, array $articleIdList): array
    {
        $categories = Category::select('categories_stocks.id', 'categories_names.name', 'categories_stocks.article_id')
            ->where('categories.account_id', $accountEntity->getAccountId())
            ->join('categories_names', 'categories.id', '=', 'categories_names.category_id')
            ->join('categories_stocks', function ($join) use ($articleIdList) {
                $join->on('categories.id', '=', 'categories_stocks.category_id')
                    ->whereIn('categories_stocks.article_id', $articleIdList);
            })
            ->get();

        return $categories->toArray();
    }

    /**
     * カテゴリとストックのリレーションを削除する
     *
     * @param array $categoryStockRelationList
     */
    public function destroyCategoriesStocks(array $categoryStockRelationList)
    {
        CategoryStock::destroy($categoryStockRelationList);
    }

    /**
     * カテゴリとストックのリレーションの件数を取得する
     *
     * @param string $categoryId
     * @return int
     */
    public function getCountCategoriesStocksByCategoryId(string $categoryId): int
    {
        return CategoryStock::where('category_id', $categoryId)->count();
    }
}
