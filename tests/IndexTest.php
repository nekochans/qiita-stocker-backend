<?php
/**
 * IndexTest
 */

namespace Tests;

/**
 * Class IndexTest
 * @package Tests
 */
class IndexTest extends TestCase
{
    /**
     * 正常系のテスト
     * 天気が取得できること
     */
    public function testSuccessIndex()
    {
        $weatherArray = array('sunny', 'cloudy', 'rainy');
        $jsonResponse = $this->get('/api/weather');

        $jsonResponse->assertJsonStructure(['weather']);

        $responseArray = json_decode($jsonResponse->content(), true);
        $this->assertTrue(in_array($responseArray['weather'], $weatherArray));

        $jsonResponse->assertStatus(200);
    }
}
