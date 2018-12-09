<?php
/**
 * CategoryNameValue
 */

namespace App\Models\Domain\Category;

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

    /**
     * カテゴリ名のバリデーションエラー時に使用するメッセージ
     *
     * @return string
     */
    public static function nameValidationErrorMessage(): string
    {
        return 'カテゴリ名は最大50文字です。カテゴリ名を短くしてください。';
    }
}
