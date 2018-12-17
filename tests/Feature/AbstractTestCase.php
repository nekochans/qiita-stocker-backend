<?php
/**
 * AbstractTestCase
 */

namespace Tests\Feature;

use Tests\TestCase;
use GuzzleHttp\Client;
use App\Eloquents\Stock;
use App\Eloquents\Account;
use App\Eloquents\Category;
use App\Eloquents\StockTag;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Tests\CreatesApplication;
use App\Eloquents\AccessToken;
use App\Eloquents\CategoryName;

use App\Eloquents\LoginSession;
use App\Eloquents\QiitaAccount;
use App\Eloquents\QiitaUserName;
use GuzzleHttp\Handler\MockHandler;

/**
 * Class AbstractTestCase
 * @package Tests\Unit
 */
abstract class AbstractTestCase extends TestCase
{
    use CreatesApplication;

    public function setUp()
    {
        parent::setUp();

        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Account::truncate();
        LoginSession::truncate();
        AccessToken::truncate();
        QiitaAccount::truncate();
        QiitaUserName::truncate();
        Category::truncate();
        CategoryName::truncate();
        Stock::truncate();
        StockTag::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function tearDown()
    {
        $this->beforeApplicationDestroyed(function () {
            \DB::disconnect();
        });

        parent::tearDown();
    }

    /**
     * テスト用のモックを設定する
     *
     * @param $responses
     */
    protected function setMockGuzzle($responses)
    {
        app()->bind(Client::class, function () use ($responses) {
            $mock = [];
            foreach ($responses as $response) {
                $mock[] = new Response($response[0], $response[1] ?? [], $response[2] ?? null);
            }
            $mock = new MockHandler($mock);
            $handler = HandlerStack::create($mock);

            return new Client(['handler' => $handler]);
        });
    }
}
