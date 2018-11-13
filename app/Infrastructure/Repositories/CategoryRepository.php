<?php
/**
 * CategoryRepository
 */

namespace App\Infrastructure\Repositories;

use App\Eloquents\Category;
use App\Eloquents\CategoryName;
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
}
