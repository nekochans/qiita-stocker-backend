<?php
/**
 * CategoryRepository
 */

namespace App\Models\Domain\Category;

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
     * @param CategoryNameValue $categoryNameValue
     * @return CategoryEntity
     */
    public function create(AccountEntity $accountEntity, CategoryNameValue $categoryNameValue): CategoryEntity;

    /**
     * カテゴリ一覧を取得する
     *
     * @param AccountEntity $accountEntity
     * @return CategoryEntities
     */
    public function search(AccountEntity $accountEntity): CategoryEntities;

    /**
     * アカウントに紐づくカテゴリを全て削除する
     *
     * @param string $accountId
     */
    public function destroyAll(string $accountId);

    /**
     * カテゴリを取得する
     *
     * @param string $categoryId
     * @param string $accountId
     * @return CategoryEntity
     */
    public function findByIdAndAccountId(string $categoryId, string $accountId): CategoryEntity;

    /**
     * カテゴリ名を更新する
     *
     * @param CategoryEntity $categoryEntity
     */
    public function updateName(CategoryEntity $categoryEntity);
}
