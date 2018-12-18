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

    /**
     * ストック一覧を取得する
     *
     * @param array $params
     * @return array
     * @throws UnauthorizedException
     * @throws \App\Models\Domain\Exceptions\LoginSessionExpiredException
     */
    public function index(array $params): array
    {
        try {
            $accountEntity = $this->findAccountEntity($params, $this->loginSessionRepository, $this->accountRepository);

            \DB::beginTransaction();

            // TODO StockEntitiesを取得する
            // TODO ストックの総数を取得する
            // TODO Linkを作成する

            \DB::commit();
        } catch (ModelNotFoundException $e) {
            throw new UnauthorizedException(LoginSessionEntity::loginSessionUnauthorizedMessage());
        } catch (\PDOException $e) {
            \DB::rollBack();
            throw $e;
        }

        $stocks = [
            [
                'id'                       => 1,
                'article_id'               => '1234567890abcdefghij',
                'title'                    => 'タイトル',
                'user_id'                  => 'test-user',
                'profile_image_url'        => 'http://test.com/test-image.jpag',
                'article_created_at'       => '2018-12-01 00:00:00.000000',
                'tags'                     => ['laravel5.6', 'laravel', 'php']
            ],
            [
                'id'                       => 2,
                'article_id'               => '1234567890abcdefghij',
                'title'                    => 'タイトル2',
                'user_id'                  => 'test-user2',
                'profile_image_url'        => 'http://test.com/test-image2.jpag',
                'article_created_at'       => '2018-12-01 00:00:00.000000',
                'tags'                     => ['laravel5.6', 'laravel', 'php']
            ]
        ];

        $totalCount = 9;
        $link = '<http://127.0.0.1/api/stocks?page=4&per_page=2>; rel="next",';
        $link .= '<http://127.0.0.1/api/stocks?page=5&per_page=2>; rel="last",';
        $link .= '<http://127.0.0.1/api/stocks?page=1&per_page=2>; rel="first",';
        $link .= '<http://127.0.0.1/api/stocks?page=2&per_page=2>; rel="prev"';

        $response = [
            'stocks'     => $stocks,
            'totalCount' => $totalCount,
            'link'       => $link
        ];

        return $response;
    }
}
