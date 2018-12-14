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
use App\Models\Domain\Account\AccountEntity;
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
     * @return mixed
     */
    public function save(string $accountId, StockValues $stockValues)
    {
        $stockValueList = $stockValues->getStockEntities();
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

    /**
     * ストック一覧を取得する
     *
     * @param AccountEntity $accountEntity
     */
    /**
     * @param AccountEntity $accountEntity
     * @return StockEntities
     */
    public function search(AccountEntity $accountEntity): StockEntities
    {
        $stocks = Stock::where('account_id', $accountEntity->getAccountId())->get();

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
    private function buildStockEntity(array $stock)
    {
        $stockTags = StockTag::where('stock_id', $stock['id'])->get();

        $stockTagNames = $stockTags->map(function (StockTag $stockTag):string {
            return $stockTag['name'];
        });

        $articleCreatedAt = new \DateTime($stock['article_created_at']);
        $stockEntityBuilder = new StockEntityBuilder();
        $stockEntityBuilder->setId($stock['id']);
        $stockEntityBuilder->setArticleId($stock['article_id']);
        $stockEntityBuilder->setTitle($stock['title']);
        $stockEntityBuilder->setUserId($stock['user_id']);
        $stockEntityBuilder->setProfileImageUrl($stock['profile_image_url']);
        $stockEntityBuilder->setArticleCreatedAt($articleCreatedAt);
        $stockEntityBuilder->setTags($stockTagNames->toArray());

        return $stockEntityBuilder->build();
    }
}
