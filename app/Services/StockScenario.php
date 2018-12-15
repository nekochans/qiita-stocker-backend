<?php
/**
 * StockScenario
 */

namespace App\Services;

use App\Models\Domain\Stock\StockValues;
use App\Models\Domain\QiitaApiRepository;
use App\Models\Domain\Stock\StockEntities;
use GuzzleHttp\Exception\RequestException;
use App\Models\Domain\Stock\StockRepository;
use App\Models\Domain\Account\AccountRepository;
use App\Models\Domain\LoginSession\LoginSessionEntity;
use App\Models\Domain\Exceptions\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Domain\LoginSession\LoginSessionRepository;

/**
 * Class StockScenario
 * @package App\Services
 */
class StockScenario
{
    use Authentication;

    /**
     * AccountRepository
     *
     * @var
     */
    private $accountRepository;

    /**
     * LoginSessionRepository
     *
     * @var
     */
    private $loginSessionRepository;

    /**
     * StockRepository
     *
     * @var
     */
    private $stockRepository;

    /**
     * QiitaApiRepository
     *
     * @var
     */
    private $qiitaApiRepository;

    /**
     * StockScenario constructor.
     * @param AccountRepository $accountRepository
     * @param LoginSessionRepository $loginSessionRepository
     * @param StockRepository $stockRepository
     * @param QiitaApiRepository $qiitaApiRepository
     */
    public function __construct(
        AccountRepository $accountRepository,
        LoginSessionRepository $loginSessionRepository,
        StockRepository $stockRepository,
        QiitaApiRepository $qiitaApiRepository
    ) {
        $this->accountRepository = $accountRepository;
        $this->loginSessionRepository = $loginSessionRepository;
        $this->stockRepository = $stockRepository;
        $this->qiitaApiRepository = $qiitaApiRepository;
    }

    /**
     * ストックを同期する
     *
     * @param array $params
     * @throws \App\Models\Domain\Exceptions\LoginSessionExpiredException
     * @throws \App\Models\Domain\Exceptions\UnauthorizedException
     */
    public function synchronize(array $params)
    {
        try {
            $accountEntity = $this->findAccountEntity($params, $this->loginSessionRepository, $this->accountRepository);

            $stockValues = $this->qiitaApiRepository->fetchStock($accountEntity->getUserName());

            \DB::beginTransaction();

            $stockEntities = $this->stockRepository->search($accountEntity->getAccountId());

            $this->synchronizeStock($stockEntities, $stockValues, $accountEntity->getAccountId());

//            $this->stockRepository->save($accountEntity->getAccountId(), $stockValues);

            \DB::commit();
        } catch (ModelNotFoundException $e) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
        } catch (RequestException $e) {
            // TODO QiitaAPIでエラーになった場合の処理を追加する
        } catch (\PDOException $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param StockEntities $stockEntities
     * @param StockValues $stockValues
     * @param string $accountId
     */
    private function synchronizeStock(StockEntities $stockEntities, StockValues $stockValues, string $accountId)
    {
        $stockValueList = $stockValues->getStockValues();
        $stockEntityList = $stockEntities->getStockEntities();

        $storedArticleIdList = [];
        foreach ($stockEntityList as $stockEntity) {
            array_push($storedArticleIdList, $stockEntity->getArticleId());
        }

        $stockValueListForSave = [];
        $fetchArticleIdList = [];

        // Insert or Update
        foreach ($stockValueList as $stockValue) {
            $fetchedArticleId = $stockValue->getArticleId();
            array_push($fetchArticleIdList, $fetchedArticleId);
            if (in_array($fetchedArticleId, $storedArticleIdList)) {
                \Log::debug('差分を更新する');
            } else {
                array_push($stockValueListForSave, $stockValue);
            }
        }

        // Insert
        $stockValuesForSave = new StockValues(...$stockValueListForSave);
        $this->stockRepository->save($accountId, $stockValuesForSave);

        // Delete
        $deleteArticleIdList = array_diff($storedArticleIdList, $fetchArticleIdList);
        $deleteArticleIdList = array_values($deleteArticleIdList);
        $this->stockRepository->delete($accountId, $deleteArticleIdList);

        // Update
    }
}
