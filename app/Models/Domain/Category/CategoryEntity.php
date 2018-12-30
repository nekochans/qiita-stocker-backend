<?php
/**
 * CategoryEntity
 */

namespace App\Models\Domain\Category;

use App\Models\Domain\Account\AccountEntity;

/**
 * Class CategoryEntity
 * @package App\Models\Domain
 */
class CategoryEntity
{
    /**
     * カテゴリID
     *
     * @var int
     */
    private $Id;

    /**
     * カテゴリ名
     *
     * @var CategoryNameValue
     */
    private $categoryNameValue;

    /**
     * CategoryEntity constructor.
     * @param CategoryEntityBuilder $builder
     */
    public function __construct(CategoryEntityBuilder $builder)
    {
        $this->Id = $builder->getId();
        $this->categoryNameValue = $builder->getCategoryNameValue();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->Id;
    }

    /**
     * @return CategoryNameValue
     */
    public function getCategoryNameValue(): CategoryNameValue
    {
        return $this->categoryNameValue;
    }

    /**
     * ストックをカテゴライズする
     *
     * @param CategoryRepository $categoryRepository
     * @param AccountEntity $accountEntity
     * @param array $articleIds
     */
    public function categorize(CategoryRepository $categoryRepository, AccountEntity $accountEntity, array $articleIds)
    {
        $this->destroyRelation($categoryRepository, $accountEntity, $articleIds);
        $this->createRelation($categoryRepository, $articleIds);
    }

    /**
     * カテゴリが持つストックのリストを取得する
     *
     * @param CategoryRepository $categoryRepository
     * @return array
     */
    public function searchHadStockList(CategoryRepository $categoryRepository): array
    {
        return $categoryRepository->searchCategoriesStocksByCategoryId($this);
    }

    /**
     * 既存のストックとその他のカテゴリとのリレーションを削除する
     *
     * @param CategoryRepository $categoryRepository
     * @param AccountEntity $accountEntity
     * @param array $articleIds
     */
    private function destroyRelation(CategoryRepository $categoryRepository, AccountEntity $accountEntity, array $articleIds)
    {
        $categorizedArticleIds = $categoryRepository->searchCategoriesStocksByArticleId($accountEntity, $this, $articleIds);
        $categoryRepository->destroyCategoriesStocks($categorizedArticleIds);
    }

    /**
     * カテゴリとストックのリレーションを作成する
     *
     * @param CategoryRepository $categoryRepository
     * @param array $articleIds
     */
    private function createRelation(CategoryRepository $categoryRepository, array $articleIds)
    {
        $stockArticleIdList = $this->searchHadStockList($categoryRepository);

        $saveArticleIds = [];
        foreach ($articleIds as $articleId) {
            if (!in_array($articleId, $stockArticleIdList)) {
                array_push($saveArticleIds, $articleId);
            }
        }

        $uniqueSaveArticleIds = array_unique($saveArticleIds);
        if ($uniqueSaveArticleIds) {
            $categoryRepository->createCategoriesStocks($this, $uniqueSaveArticleIds);
        }
    }

    /**
     * カテゴリIDのバリデーションエラー時に利用するメッセージ
     *
     * @return string
     */
    public static function categoryIdValidationErrorMessage(): string
    {
        return '不正なリクエストが行われました。';
    }

    /**
     * カテゴリが作成されていなかった場合に使用するメッセージ
     *
     * @return string
     */
    public static function categoryNotFoundMessage(): string
    {
        return '不正なリクエストが行われました。';
    }

    /**
     * カテゴリが作成されていなかった場合に使用するメッセージ
     *
     * @return string
     */
    public static function createCategoriesStocksValidationErrorMessage(): string
    {
        return '不正なリクエストが行われました。';
    }
}
