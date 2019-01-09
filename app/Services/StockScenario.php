<?php
/**
 * StockScenario
 */

namespace App\Services;

use App\Models\Domain\Stock\StockValues;
use App\Models\Domain\QiitaApiRepository;
use GuzzleHttp\Exception\RequestException;
use App\Models\Domain\Stock\LinkHeaderValue;
use App\Models\Domain\Category\CategoryEntity;
use App\Models\Domain\Stock\LinkHeaderService;
use App\Models\Domain\Stock\StockSpecification;
use App\Models\Domain\Account\AccountRepository;
use App\Models\Domain\Category\CategoryRepository;
use App\Models\Domain\Exceptions\ValidationException;
use App\Models\Domain\LoginSession\LoginSessionEntity;
use App\Models\Domain\Exceptions\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Domain\LoginSession\LoginSessionRepository;
use App\Models\Domain\Exceptions\CategoryNotFoundException;
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
     * QiitaApiRepository
     *
     * @var
     */
    private $qiitaApiRepository;


    /**
     * CategoryRepository
     *
     * @var
     */
    private $categoryRepository;

    /**
     * StockScenario constructor.
     * @param AccountRepository $accountRepository
     * @param LoginSessionRepository $loginSessionRepository
     * @param QiitaApiRepository $qiitaApiRepository
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        AccountRepository $accountRepository,
        LoginSessionRepository $loginSessionRepository,
        QiitaApiRepository $qiitaApiRepository,
        CategoryRepository $categoryRepository
    ) {
        $this->accountRepository = $accountRepository;
        $this->loginSessionRepository = $loginSessionRepository;
        $this->qiitaApiRepository = $qiitaApiRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * ストック一覧を取得する
     *
     * @param array $params
     * @return array
     * @throws ServiceUnavailableException
     * @throws UnauthorizedException
     * @throws ValidationException
     * @throws \App\Models\Domain\Exceptions\LoginSessionExpiredException
     */
    public function index(array $params): array
    {
        try {
            $errors = StockSpecification::canFetchStocks($params);
            if ($errors) {
                throw new ValidationException(StockValues::searchStocksErrorMessage(), $errors);
            }

            $accountEntity = $this->findAccountEntity($params, $this->loginSessionRepository, $this->accountRepository);

            $fetchStocksValue = $this->qiitaApiRepository->fetchStocks($accountEntity, $params['page'], $params['perPage']);

            $stockValueList = $fetchStocksValue->getStockValues();
            $stockArticleIdList = [];
            foreach ($stockValueList as $stockValue) {
                array_push($stockArticleIdList, $stockValue->getArticleId());
            }

            $categoryArticleIds = $this->categoryRepository->searchCategoriesStocksAllByArticleId($accountEntity, $stockArticleIdList);
        } catch (ModelNotFoundException $e) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
        } catch (RequestException $e) {
            throw new ServiceUnavailableException();
        } catch (\PDOException $e) {
            throw $e;
        }

        $stocks = [];

        foreach ($stockValueList as $stockValue) {
            $stock = [
                'stock' => [
                    'article_id'               => $stockValue->getArticleId(),
                    'title'                    => $stockValue->getTitle(),
                    'user_id'                  => $stockValue->getUserId(),
                    'profile_image_url'        => $stockValue->getProfileImageUrl(),
                    'article_created_at'       => $stockValue->getArticleCreatedAt()->format('Y-m-d H:i:s.u'),
                    'tags'                     => $stockValue->getTags(),
                ]
            ];

            $keyIndex = array_search($stockValue->getArticleId(), array_column($categoryArticleIds, 'article_id'));
            if ($keyIndex !== false) {
                $stock['category'] = [
                    'categoryId' => $categoryArticleIds[$keyIndex]['id'],
                    'name'       => $categoryArticleIds[$keyIndex]['name'],
                ];
            }

            array_push($stocks, $stock);
        }

        $linkList = $this->buildLinkHeaderList($params['uri'], $params['page'], $params['perPage'], $fetchStocksValue->getTotalCount());
        $link = implode(', ', $linkList);

        $response = [
            'stocks'     => $stocks,
            'totalCount' => $fetchStocksValue->getTotalCount(),
            'link'       => $link
        ];

        return $response;
    }

    /**
     * カテゴライズされたストック一覧を取得する
     *
     * @param array $params
     * @return array
     * @throws CategoryNotFoundException
     * @throws UnauthorizedException
     * @throws ValidationException
     * @throws \App\Models\Domain\Exceptions\LoginSessionExpiredException
     */
    public function showCategorized(array $params): array
    {
        try {
            $errors = StockSpecification::canFetchCategorizedStocks($params);
            if ($errors) {
                throw new ValidationException(StockValues::searchStocksErrorMessage(), $errors);
            }

            $accountEntity = $this->findAccountEntity($params, $this->loginSessionRepository, $this->accountRepository);
        } catch (ModelNotFoundException $e) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
        } catch (\PDOException $e) {
            throw $e;
        }

        try {
            $categoryEntity = $accountEntity->findHasCategoryEntity($this->categoryRepository, $params['id']);

            $limit = $params['perPage'];
            $offset = ($params['page'] - 1) * $limit;

            $categoryStockEntities = $categoryEntity->searchHasCategoryStockEntities($this->categoryRepository, $limit, $offset);
            $totalCount = $this->categoryRepository->getCountCategoriesStocksByCategoryId($categoryEntity->getId());
        } catch (ModelNotFoundException $e) {
            throw new CategoryNotFoundException(CategoryEntity::categoryNotFoundMessage());
        } catch (\PDOException $e) {
            \DB::rollBack();
            throw $e;
        }

        $CategoryStockEntityList = $categoryStockEntities->getCategoryStockEntities();

        $linkList = $this->buildLinkHeaderList($params['uri'], $params['page'], $params['perPage'], $totalCount);
        $link = implode(', ', $linkList);

        $stocks = [];
        foreach ($CategoryStockEntityList as $categoryStockEntity) {
            $stock = [
                'id'                       => $categoryStockEntity->getId(),
                'article_id'               => $categoryStockEntity->getStockValue()->getArticleId(),
                'title'                    => $categoryStockEntity->getStockValue()->getTitle(),
                'user_id'                  => $categoryStockEntity->getStockValue()->getUserId(),
                'profile_image_url'        => $categoryStockEntity->getStockValue()->getProfileImageUrl(),
                'article_created_at'       => $categoryStockEntity->getStockValue()->getArticleCreatedAt()->format('Y-m-d H:i:s.u'),
                'tags'                     => $categoryStockEntity->getStockValue()->getTags(),
            ];
            array_push($stocks, $stock);
        }

        $response = [
            'stocks'     => $stocks,
            'totalCount' => $totalCount,
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
