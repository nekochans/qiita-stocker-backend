<?php
/**
 * AccountSpecification
 */

namespace App\Models\Domain\Account;

/**
 * Class AccountSpecification
 * @package App\Models\Domain
 */
class AccountSpecification
{
    /**
     * アカウント作成可能か確認する
     *
     * @param array $requestArray
     * @return array
     */
    public static function canCreate(array $requestArray): array
    {
        $validator = \Validator::make($requestArray, [
            'accessToken' => 'required|regex:/^[a-z0-9]+$/|min:40|max:64',
            'permanentId' => 'required|integer|min:1|max:4294967294',
        ]);

        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        return [];
    }
}
