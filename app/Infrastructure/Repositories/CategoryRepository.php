<?php
/**
 * CategoryRepository
 */

namespace App\Infrastructure\Repositories;

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
        $categoryEntityBuilder = new CategoryEntityBuilder();
        $categoryEntityBuilder->setId('1');
        $categoryEntityBuilder->setCategoryNameValue($categoryNameValue);
        return $categoryEntityBuilder->build();
    }
}
