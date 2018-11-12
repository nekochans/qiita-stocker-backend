<?php
/**
 * CategoryRepository
 */

namespace App\Models\Domain\category;

use App\Models\Domain\AccountEntity;

/**
 * Interface CategoryRepository
 * @package App\Models\Domain
 */
interface CategoryRepository
{
    /**
     * カテゴリを作成する
     *
     * @param AccountEntity $accountEntity
     * @param CategoryValue $categoryValue
     * @return CategoryEntity
     */
    public function create(AccountEntity $accountEntity, CategoryValue $categoryValue): CategoryEntity;
}
