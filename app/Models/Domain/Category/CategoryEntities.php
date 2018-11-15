<?php
/**
 * CategoryEntities
 */

namespace App\Models\Domain\Category;

/**
 * Class CategoryEntities
 * @package App\Models\Domain\Category
 */
class CategoryEntities
{
    /**
     *
     * @var CategoryEntity[]
     */
    private $categoryEntities;

    /**
     * CategoryEntities constructor.
     * @param CategoryEntity ...$categoryEntities
     */
    public function __construct(CategoryEntity ...$categoryEntities)
    {
        $this->categoryEntities = $categoryEntities;
    }

    /**
     * @return CategoryEntity[]
     */
    public function getCategoryEntities(): array
    {
        return $this->categoryEntities;
    }
}
