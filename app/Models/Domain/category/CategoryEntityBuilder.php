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
     * @var string
     */
    private $name;

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
     * @return CategoryEntity
     */
    public function build(): CategoryEntity
    {
        return new CategoryEntity($this);
    }
}
