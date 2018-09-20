<?php
/**
 * WeatherService
 */

namespace App\Services;

/**
 * Class WeatherService
 * @package App\Services
 */
class WeatherService
{
    /**
     * 天気を取得する
     * @return array
     */
    public function fetchWeather(): array
    {
        $weatherArray = ['sunny', 'cloudy', 'rainy'];

        $key = array_rand($weatherArray);

        return ['weather' => $weatherArray[$key]];
    }
}
