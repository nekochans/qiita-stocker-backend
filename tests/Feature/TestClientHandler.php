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
        $body = file_get_contents(dirname(__FILE__) . '/StockSynchronizeTest.json');
        $mock = new MockHandler([
            new Response(200, [], $body)
        ]);
        return HandlerStack::create($mock);
    }
}
