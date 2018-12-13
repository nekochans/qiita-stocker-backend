<?php
/**
 * StockRepository
 */

namespace App\Infrastructure\Repositories\Eloquent;

use App\Eloquents\Stock;
use App\Eloquents\StockTag;
use App\Models\Domain\Stock\StockValue;
use App\Models\Domain\Stock\StockValues;

/**
 * Class StockRepository
 * @package App\Infrastructure\Repositories\Eloquent
 */
class StockRepository implements \App\Models\Domain\Stock\StockRepository
{
    /**
     * ストックを保存する
     *
     * @param string $accountId
     * @param StockValues $stockEntities
     * @return mixed
     */
    public function save(string $accountId, StockValues $stockEntities)
    {
        $stockValueList = $stockEntities->getStockEntities();
        foreach ($stockValueList as $stockValue) {
            $stockId = $this->saveStocks($accountId, $stockValue);
            $this->saveStocksTags($stockId, $stockValue->getTags());
        }
    }

    /**
     * stocks テーブルにデータを保存する
     *
     * @param string $accountId
     * @param StockValue $stockValue
     * @return int
     */
    private function saveStocks(string $accountId, StockValue $stockValue): int
    {
        $stock = new Stock();
        $stock->article_id = $stockValue->getArticleId();
        $stock->title = $stockValue->getTitle();
        $stock->user_id = $stockValue->getUserId();
        $stock->profile_image_url = $stockValue->getProfileImageUrl();
        $stock->article_created_at = $stockValue->getArticleCreatedAt();
        $stock->account_id = $accountId;
        $stock->save();
        $stockId = $stock->getAttribute('id');

        return $stockId;
    }

    /**
     * stocks_tags テーブルにデータを保存する
     *
     * @param int $stockId
     * @param array $tags
     */
    private function saveStocksTags(int $stockId, array $tags)
    {
        foreach ($tags as $tag) {
            $stockTag = new StockTag();
            $stockTag->stock_id = $stockId;
            $stockTag->name = $tag;
            $stockTag->save();
        }
    }
}
