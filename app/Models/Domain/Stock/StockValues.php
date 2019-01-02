<?php
/**
 * StockValues
 */

namespace App\Models\Domain\Stock;

/**
 * Class StockValues
 * @package App\Models\Domain\Stock
 */
class StockValues
{
    /**
     *
     * @var StockValue[]
     */
    private $stockValues;

    /**
     * StockEntities constructor.
     * @param StockValue ...$stockValues
     */
    public function __construct(StockValue ...$stockValues)
    {
        $this->stockValues = $stockValues;
    }

    /**
     * @return StockValue[]
     */
    public function getStockValues(): array
    {
        return $this->stockValues;
    }

    /**
     * ストック取得時のバリデーションエラーの場合に使用するメッセージ
     *
     * @return string
     */
    public static function searchStocksErrorMessage(): string
    {
        return '不正なリクエストが行われました。';
    }
}
