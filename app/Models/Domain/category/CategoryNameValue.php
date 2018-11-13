<?php
/**
 * CategoryNameValue
 */

namespace App\Models\Domain\category;

/**
 * Class CategoryNameValue
 * @package App\Models\Domain
 */
class CategoryNameValue
{
    /**
     * カテゴリ名
     *
     * @var string
     */
    private $name;

    /**
     * CategoryNameValue constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
