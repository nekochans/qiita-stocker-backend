<?php
/**
 * CategoryValue
 */

namespace App\Models\Domain\category;

/**
 * Class CategoryValue
 * @package App\Models\Domain
 */
class CategoryValue
{
    /**
     * カテゴリ名
     *
     * @var string
     */
    private $name;

    /**
     * CategoryValue constructor.
     * @param CategoryValueBuilder $builder
     */
    public function __construct(CategoryValueBuilder $builder)
    {
        $this->name = $builder->getName();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
