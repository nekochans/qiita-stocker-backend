<?php
/**
 * TestClientHandler
 */

namespace Tests\Feature;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;

class TestClientHandler
{
    /**
     * テスト用のHandlerを生成する
     *
     * @return HandlerStack
     */
    public static function create(): HandlerStack
    {
        $firstPage = file_get_contents(dirname(__FILE__) . '/StockSynchronizeTestMockFirst.json');
        $nestPage = file_get_contents(dirname(__FILE__) . '/StockSynchronizeTestMockNext.json');
        $mock = new MockHandler([
            new Response(200, ['total-count' => '101'], $firstPage),
            new Response(200, ['total-count' => '101'], $nestPage)
        ]);
        return HandlerStack::create($mock);
    }
}
