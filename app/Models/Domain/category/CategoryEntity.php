<?php
/**
 * CategoryEntity
 */

namespace App\Models\Domain\category;

/**
 * Class CategoryEntity
 * @package App\Models\Domain
 */
class CategoryEntity
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
     * @var string
     */
    private $name;

    public function __construct(CategoryEntityBuilder $builder)
    {
        $this->Id = $builder->getId();
        $this->name = $builder->getName();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->Id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
