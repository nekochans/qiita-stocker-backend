<?php
/**
 * StockScenario
 */

namespace App\Services;

use App\Models\Domain\QiitaApiRepository;
use GuzzleHttp\Exception\RequestException;
use App\Models\Domain\Stock\StockRepository;
use App\Models\Domain\Account\AccountRepository;
use App\Models\Domain\LoginSession\LoginSessionEntity;
use App\Models\Domain\Exceptions\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Domain\LoginSession\LoginSessionRepository;
use App\Models\Domain\Exceptions\ServiceUnavailableException;

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
     * @throws ServiceUnavailableException
     * @throws UnauthorizedException
     * @throws \App\Models\Domain\Exceptions\LoginSessionExpiredException
     */
    public function synchronize(array $params)
    {
        try {
            $accountEntity = $this->findAccountEntity($params, $this->loginSessionRepository, $this->accountRepository);

            $stockValues = $this->qiitaApiRepository->fetchStock($accountEntity->getUserName());

            \DB::beginTransaction();

            $stockEntities = $this->stockRepository->search($accountEntity->getAccountId());
            $stockEntities->synchronize($this->stockRepository, $stockValues, $accountEntity->getAccountId());

            \DB::commit();
        } catch (ModelNotFoundException $e) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
        } catch (RequestException $e) {
            throw new ServiceUnavailableException();
        } catch (\PDOException $e) {
            \DB::rollBack();
            throw $e;
        }
    }
}
