<?php
/**
 * CategoryValueBuilder
 */

namespace App\Models\Domain\category;

/**
 * Class CategoryValueBuilder
 * @package App\Models\Domain
 */
class CategoryValueBuilder
{
    /**
     * カテゴリ名
     *
     * @var string
     */
    private $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return CategoryValue
     */
    public function build(): CategoryValue
    {
        return new CategoryValue($this);
    }
}
