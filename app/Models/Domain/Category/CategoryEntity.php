<?php
/**
 * CategoryEntity
 */

namespace App\Models\Domain\Category;

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
