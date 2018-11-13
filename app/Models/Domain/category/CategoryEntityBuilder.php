<?php
/**
 * CategoryEntityBuilder
 */

namespace App\Models\Domain\category;

/**
 * Class CategoryEntityBuilder
 * @package App\Models\Domain
 */
class CategoryEntityBuilder
{
    /**
     * カテゴリID
     *
     * @var string
     */
    private $Id;

    /**
     * カテゴリ名
     *
     * @var CategoryNameValue
     */
    private $categoryNameValue;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->Id;
    }

    /**
     * @param string $Id
     */
    public function setId(string $Id): void
    {
        $this->Id = $Id;
    }

    /**
     * @return CategoryNameValue
     */
    public function getCategoryNameValue(): CategoryNameValue
    {
        return $this->categoryNameValue;
    }

    /**
     * @param CategoryNameValue $categoryNameValue
     */
    public function setCategoryNameValue(CategoryNameValue $categoryNameValue): void
    {
        $this->categoryNameValue = $categoryNameValue;
    }

    /**
     * @return CategoryEntity
     */
    public function build(): CategoryEntity
    {
        return new CategoryEntity($this);
    }
}
