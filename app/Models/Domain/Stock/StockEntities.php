<?php
/**
 * StockEntities
 */

namespace App\Models\Domain\Stock;

/**
 * Class StockEntities
 * @package App\Models\Domain\Stock
 */
class StockEntities
{
    /**
     *
     * @var StockEntity[]
     */
    private $stockEntities;

    /**
     * StockEntities constructor.
     * @param StockEntity ...$stockEntities
     */
    public function __construct(StockEntity ...$stockEntities)
    {
        $this->stockEntities = $stockEntities;
    }

    /**
     * @return StockEntity[]
     */
    public function getStockEntities(): array
    {
        return $this->stockEntities;
    }

    /**
     * ストックの同期処理を行う
     *
     * @param StockRepository $stockRepository
     * @param StockValues $stockValues
     * @param string $accountId
     */
    public function synchronize(StockRepository $stockRepository, StockValues $stockValues, string $accountId)
    {
        $stockValueList = $stockValues->getStockValues();
        $stockEntityList = $this->getStockEntities();

        $storedArticleIdList = [];
        foreach ($stockEntityList as $stockEntity) {
            array_push($storedArticleIdList, $stockEntity->getStockValue()->getArticleId());
        }

        $fetchArticleIdList = [];
        $saveStockValueList = [];
        $updateStockEntityList = [];

        foreach ($stockValueList as $stockValue) {
            $fetchedArticleId = $stockValue->getArticleId();
            array_push($fetchArticleIdList, $fetchedArticleId);

            $key = array_search($fetchedArticleId, $storedArticleIdList);

            if ($key !== false) {
                $stockEntity = $stockEntityList[$key];

                if ($stockEntity->isUpdatedExceptTag($stockValue)) {
                    $updateStockEntityBuilder = new StockEntityBuilder();
                    $updateStockEntityBuilder->setId($stockEntity->getId());
                    $updateStockEntityBuilder->setStockValue($stockValue);
                    $updateStockEntity = $updateStockEntityBuilder->build();
                    array_push($updateStockEntityList, $updateStockEntity);
                }

                $this->insertTags($stockEntity, $stockValue, $stockRepository);
                $this->deleteTags($stockEntity, $stockValue, $stockRepository);
            } else {
                array_push($saveStockValueList, $stockValue);
            }
        }

        $saveStockValues = new StockValues(...$saveStockValueList);
        $stockRepository->save($accountId, $saveStockValues);

        $updateStockEntities = new StockEntities(...$updateStockEntityList);
        $stockRepository->update($updateStockEntities);

        $deleteArticleIdList = array_diff($storedArticleIdList, $fetchArticleIdList);
        $deleteArticleIdList = array_values($deleteArticleIdList);
        $stockRepository->delete($accountId, $deleteArticleIdList);
    }

    /**
     * タグを削除する
     *
     * @param StockEntity $stockEntity
     * @param StockValue $stockValue
     * @param StockRepository $stockRepository
     */
    private function deleteTags(StockEntity $stockEntity, StockValue $stockValue, StockRepository $stockRepository)
    {
        $deleteTagList = array_diff($stockEntity->getStockValue()->getTags(), $stockValue->getTags());
        $deleteTagList = array_values($deleteTagList);

        if ($deleteTagList) {
            $stockRepository->deleteStocksTags($stockEntity->getId(), $deleteTagList);
        }
    }

    /**
     * タグを追加する
     *
     * @param StockEntity $stockEntity
     * @param StockValue $stockValue
     * @param StockRepository $stockRepository
     */
    private function insertTags(StockEntity $stockEntity, StockValue $stockValue, StockRepository $stockRepository)
    {
        $insertTagList = array_diff($stockValue->getTags(), $stockEntity->getStockValue()->getTags());
        $insertTagList = array_values($insertTagList);

        if ($insertTagList) {
            $stockRepository->saveStocksTags($stockEntity->getId(), $insertTagList);
        }
    }
}
