<?php
/**
 * StockRepository
 */

namespace App\Infrastructure\Repositories\Eloquent;

use App\Eloquents\Stock;
use App\Eloquents\StockTag;
use App\Models\Domain\Stock\StockValue;
use App\Models\Domain\Stock\StockEntity;
use App\Models\Domain\Stock\StockValues;
use App\Models\Domain\Stock\StockEntities;
use App\Models\Domain\Stock\StockValueBuilder;
use App\Models\Domain\Stock\StockEntityBuilder;

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
     * @param StockValues $stockValues
     */
    public function save(string $accountId, StockValues $stockValues)
    {
        $stockValueList = $stockValues->getStockValues();
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
     * ストック一覧を取得する
     *
     * @param string $accountId
     * @return StockEntities
     */
    public function search(string $accountId): StockEntities
    {
        $stocks = Stock::where('account_id', $accountId)->get();

        $stockEntityList = $stocks->map(function (Stock $stock): StockEntity {
            return $this->buildStockEntity($stock->toArray());
        });

        $stockEntities = new StockEntities(...$stockEntityList->toArray());
        return $stockEntities;
    }

    /**
     * StockEntity を作成する
     *
     * @param array $stock
     * @return StockEntity
     */
    private function buildStockEntity(array $stock): StockEntity
    {
        $stockTags = StockTag::where('stock_id', $stock['id'])->get();

        $stockTagNames = $stockTags->map(function (StockTag $stockTag):string {
            return $stockTag['name'];
        });

        $articleCreatedAt = new \DateTime($stock['article_created_at']);
        $stockValueBuilder = new StockValueBuilder();
        $stockValueBuilder->setArticleId($stock['article_id']);
        $stockValueBuilder->setTitle($stock['title']);
        $stockValueBuilder->setUserId($stock['user_id']);
        $stockValueBuilder->setProfileImageUrl($stock['profile_image_url']);
        $stockValueBuilder->setArticleCreatedAt($articleCreatedAt);
        $stockValueBuilder->setTags($stockTagNames->toArray());
        $stockValue = $stockValueBuilder->build();

        $stockEntityBuilder = new StockEntityBuilder();
        $stockEntityBuilder->setId($stock['id']);
        $stockEntityBuilder->setStockValue($stockValue);

        return $stockEntityBuilder->build();
    }

    /**
     * ストックを削除する
     *
     * @param string $accountId
     * @param array $articleIdList
     */
    public function delete(string $accountId, array $articleIdList)
    {
        $stocks = Stock::where('account_id', $accountId)->whereIn('article_id', $articleIdList);

        $stockIdList = $stocks->get()->pluck('id');
        StockTag::whereIn('stock_id', $stockIdList)->delete();

        $stocks->delete();
    }

    /**
     * ストックを更新する
     *
     * @param StockEntities $stockEntities
     */
    public function update(StockEntities $stockEntities)
    {
        $stockEntityList = $stockEntities->getStockEntities();

        foreach ($stockEntityList as $stockEntity) {
            $stock = Stock::find($stockEntity->getId());
            $stock->article_id = $stockEntity->getStockValue()->getArticleId();
            $stock->title = $stockEntity->getStockValue()->getTitle();
            $stock->user_id = $stockEntity->getStockValue()->getUserId();
            $stock->profile_image_url = $stockEntity->getStockValue()->getProfileImageUrl();
            $stock->article_created_at = $stockEntity->getStockValue()->getArticleCreatedAt();
            $stock->save();
        }
    }

    /**
     * stocks_tags テーブルにデータを保存する
     *
     * @param int $stockId
     * @param array $tags
     */
    public function saveStocksTags(int $stockId, array $tags)
    {
        foreach ($tags as $tag) {
            $stockTag = new StockTag();
            $stockTag->stock_id = $stockId;
            $stockTag->name = $tag;
            $stockTag->save();
        }
    }

    /**
     * stocks_tags テーブルからデータを削除する
     *
     * @param int $stockId
     * @param array $tags
     */
    public function deleteStocksTags(int $stockId, array $tags)
    {
        $stockTag = StockTag::where('stock_id', $stockId)->whereIn('name', $tags);
        $stockTag->delete();
    }
}
