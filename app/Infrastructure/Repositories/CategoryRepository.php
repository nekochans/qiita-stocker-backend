<?php
/**
 * CategoryRepository
 */

namespace App\Infrastructure\Repositories;

use App\Eloquents\Category;
use App\Eloquents\CategoryName;
use Illuminate\Support\Collection;
use App\Models\Domain\AccountEntity;
use App\Models\Domain\Category\CategoryEntity;
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
     * @return Collection
     */
    public function search(AccountEntity $accountEntity): Collection
    {
        $categories = Category::select('categories.id', 'categories_names.name')
            ->where('account_id', $accountEntity->getAccountId())
            ->join('categories_names', 'categories.id', '=', 'categories_names.category_id')
            ->get();

        $categoryEntities = $categories->map(function ($category): CategoryEntity {
            return $this->buildCategoryEntity($category);
        });

        return $categoryEntities;
    }

    /**
     * CategoryEntityを作成する
     *
     * @param Category $eloquentCategory
     * @return CategoryEntity
     */
    public function buildCategoryEntity(Category $eloquentCategory): CategoryEntity
    {
        $categoryNameVale = new CategoryNameValue($eloquentCategory->name);
        $categoryEntityBuilder = new CategoryEntityBuilder();
        $categoryEntityBuilder->setId($eloquentCategory->id);
        $categoryEntityBuilder->setCategoryNameValue($categoryNameVale);

        return $categoryEntityBuilder->build();
    }
}
