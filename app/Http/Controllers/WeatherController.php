<?php
/**
 * WeatherController
 */

namespace App\Http\Controllers;

use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;

/**
 * Class WeatherController
 * @package App\Http\Controllers
 */
class WeatherController extends Controller
{
    /**
     * WeatherService
     * @var
     */
    protected $weatherService;

    /**
     * WeatherController constructor.
     * @param WeatherService $weatherService
     */
    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * 天気を取得する
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $response = $this->weatherService->fetchWeather();
        return response()->json($response);
    }
}
