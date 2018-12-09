<?php
/**
 * CategoryRepository
 */

namespace App\Infrastructure\Repositories;

use App\Eloquents\Category;
use App\Eloquents\CategoryName;
use App\Models\Domain\Account\AccountEntity;
use App\Models\Domain\Category\CategoryEntity;
use App\Models\Domain\Category\CategoryEntities;
use App\Models\Domain\Category\CategoryNameValue;
use App\Models\Domain\Category\CategoryEntityBuilder;

/**
 * Class CategoryRepository
 * @package App\Infrastructure\Repositories
 */
class CategoryRepository implements \App\Models\Domain\Category\CategoryRepository
{
    public function create(AccountEntity $accountEntity, CategoryNameValue $categoryNameValue): CategoryEntity
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

        $categories->delete();
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
}
