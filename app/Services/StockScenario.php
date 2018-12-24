<?php
/**
 * StockScenario
 */

namespace App\Services;

use App\Models\Domain\QiitaApiRepository;
use App\Models\Domain\Stock\StockEntities;
use GuzzleHttp\Exception\RequestException;
use App\Models\Domain\Stock\LinkHeaderValue;
use App\Models\Domain\Stock\StockRepository;
use App\Models\Domain\Stock\LinkHeaderService;
use App\Models\Domain\Stock\StockSpecification;
use App\Models\Domain\Account\AccountRepository;
use App\Models\Domain\Exceptions\ValidationException;
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
    // TODO 削除する
//    public function synchronize(array $params)
//    {
//        try {
//            $accountEntity = $this->findAccountEntity($params, $this->loginSessionRepository, $this->accountRepository);
//
//            $stockValues = $this->qiitaApiRepository->fetchStock($accountEntity->getUserName());
//
//            \DB::beginTransaction();
//
//            $stockEntities = $this->stockRepository->searchByAccountId($accountEntity->getAccountId());
//            $stockEntities->synchronize($this->stockRepository, $stockValues, $accountEntity->getAccountId());
//
//            \DB::commit();
//        } catch (ModelNotFoundException $e) {
//            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
//        } catch (RequestException $e) {
//            throw new ServiceUnavailableException();
//        } catch (\PDOException $e) {
//            \DB::rollBack();
//            throw $e;
//        }
//    }

    /**
     * ストック一覧を取得する
     *
     * @param array $params
     * @return array
     * @throws UnauthorizedException
     * @throws ValidationException
     * @throws \App\Models\Domain\Exceptions\LoginSessionExpiredException
     */
    public function index(array $params): array
    {
        try {
            $errors = StockSpecification::canfetchStocks($params);
            if ($errors) {
                throw new ValidationException(StockEntities::searchStocksErrorMessage(), $errors);
            }

            $accountEntity = $this->findAccountEntity($params, $this->loginSessionRepository, $this->accountRepository);

            $fetchResponse = $this->qiitaApiRepository->fetchStock($accountEntity->getUserName(), $params['page'], $params['perPage']);
        } catch (ModelNotFoundException $e) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
        } catch (\PDOException $e) {
            throw $e;
        }

        $stockValueList = $fetchResponse['stockValues']->getStockValues();
        $stocks = [];

        foreach ($stockValueList as $stockValue) {
            $stock = [
                'article_id'               => $stockValue->getArticleId(),
                'title'                    => $stockValue->getTitle(),
                'user_id'                  => $stockValue->getUserId(),
                'profile_image_url'        => $stockValue->getProfileImageUrl(),
                'article_created_at'       => $stockValue->getArticleCreatedAt()->format('Y-m-d H:i:s.u'),
                'tags'                     => $stockValue->getTags(),
            ];

            array_push($stocks, $stock);
        }

        $linkList = $this->buildLinkHeaderList($params['uri'], $params['page'], $params['perPage'], $fetchResponse['totalCount']);
        $link = implode(', ', $linkList);

        $response = [
            'stocks'     => $stocks,
            'totalCount' => $fetchResponse['totalCount'],
            'link'       => $link
        ];

        return $response;
    }

    /**
     * Linkヘッダーのリストを作成する
     *
     * @param string $uriBase
     * @param int $page
     * @param int $perPage
     * @param int $totalCount
     * @return array
     */
    private function buildLinkHeaderList(string $uriBase, int $page, int $perPage, int $totalCount): array
    {
        $totalPage = ceil($totalCount / $perPage);
        $links = [];

        if (LinkHeaderService::hasNextPage($page, $totalPage)) {
            $nextPage = $page + 1;
            $nextLinkHeaderValue = new LinkHeaderValue($uriBase, $nextPage, $perPage, 'next');
            $links[] = $nextLinkHeaderValue->buildLink();
        }

        if (LinkHeaderService::hasLastPage($page, $totalPage)) {
            $lastLinkHeaderValue = new LinkHeaderValue($uriBase, $totalPage, $perPage, 'last');
            $links[] = $lastLinkHeaderValue->buildLink();
        }

        if (LinkHeaderService::hasFirstPage($page)) {
            $firstLinkHeaderValue = new LinkHeaderValue($uriBase, 1, $perPage, 'first');
            $links[] = $firstLinkHeaderValue->buildLink();
        }

        if (LinkHeaderService::hasPrevPage($page)) {
            $prevPage = $page - 1;
            $prevLinkHeaderValue = new LinkHeaderValue($uriBase, $prevPage, $perPage, 'prev');
            $links[] = $prevLinkHeaderValue->buildLink();
        }

        return $links;
    }
}
