<?php
/**
 * CategorySpecification
 */

namespace App\Models\Domain\Category;

/**
 * Class CategorySpecification
 * @package App\Models\Domain\Category
 */
class CategorySpecification
{
    /**
     * CategoryNameValue が作成可能か確認する
     *
     * @param array $requestArray
     * @return array
     */
    public static function canCreateCategoryNameValue(array $requestArray): array
    {
        $validator = \Validator::make($requestArray, [
            'name' => 'required|max:50',
        ]);

        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        return [];
    }

    /**
     * CategoryEntity が作成可能か確認する
     *
     * @param array $requestArray
     * @return array
     */
    public static function canSetCategoryEntityId(array $requestArray): array
    {
        $validator = \Validator::make($requestArray, [
            'id'   => 'required|integer|min:1|max:18446744073709551615' // 符号無しBIGINTの最大値
        ]);

        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        return [];
    }

    /**
     * CategoryEntity が作成可能か確認する
     *
     * @param array $requestArray
     * @return array
     */
    public static function canCreateCategoriesStocks(array $requestArray): array
    {
        $validator = \Validator::make($requestArray, [
            'articleIds'     => 'required',
            'articleIds.*'   => 'required|regex:/^[0-9a-f]+$/|min:20|max:20'
        ]);

        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        return [];
    }
}
