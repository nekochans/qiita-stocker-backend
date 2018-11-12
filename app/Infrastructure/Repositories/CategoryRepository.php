<?php
/**
 * CategoryRepository
 */

namespace App\Infrastructure\Repositories;

use App\Models\Domain\AccountEntity;
use App\Models\Domain\category\CategoryValue;
use App\Models\Domain\category\CategoryEntity;
use App\Models\Domain\category\CategoryEntityBuilder;

/**
 * Class CategoryRepository
 * @package App\Infrastructure\Repositories
 */
class CategoryRepository implements \App\Models\Domain\category\CategoryRepository
{
    public function create(AccountEntity $accountEntity, CategoryValue $categoryValue): CategoryEntity
    {
        $categoryEntityBuilder = new CategoryEntityBuilder();
        $categoryEntityBuilder->setId('1');
        $categoryEntityBuilder->setName($categoryValue->getName());
        return $categoryEntityBuilder->build();
    }
}
