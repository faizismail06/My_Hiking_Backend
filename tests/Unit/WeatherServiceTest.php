<?php

namespace Tests\Unit;

use App\Services\WeatherService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherServiceTest extends TestCase
{
    public function test_get_current_weather_maps_scores_and_uses_cache(): void
    {
        Cache::flush();

        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'weather_code' => 61,
                    'temperature_2m' => 18.4,
                    'wind_speed_10m' => 12.2,
                    'precipitation_probability' => 60,
                ],
            ], 200),
        ]);

        $service = new WeatherService();

        $first = $service->getCurrentWeather(-7.242, 109.208);
        $second = $service->getCurrentWeather(-7.242, 109.208);

        $this->assertSame(61, $first['code']);
        $this->assertSame('Rain', $first['condition']);
        $this->assertSame(3, $first['weather_score']);
        $this->assertSame(2, $first['wind_score']);
        $this->assertEquals(2.7, $first['weather_score_final']);
        $this->assertSame($first, $second);

        Http::assertSentCount(1);
    }

    public function test_get_current_weather_uses_openweather_when_key_is_set(): void
    {
        Cache::flush();
        config(['services.openweather.key' => 'test_api_key']);

        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'weather' => [
                    ['id' => 500, 'main' => 'Rain'],
                ],
                'main' => [
                    'temp' => 22.5,
                ],
                'wind' => [
                    'speed' => 3.0, // 3 m/s = 10.8 km/h
                ],
                'pop' => 0.75,
            ], 200),
        ]);

        $service = new WeatherService();
        $res = $service->getCurrentWeather(-7.242, 109.208);

        $this->assertSame('openweather', $res['source']);
        $this->assertSame(61, $res['code']);
        $this->assertSame('Rain', $res['condition']);
        $this->assertSame(22.5, $res['temperature']);
        $this->assertSame(10.8, $res['wind_speed']);
        $this->assertSame(75.0, $res['precipitation_probability']);
        $this->assertSame(3, $res['weather_score']);
        $this->assertSame(2, $res['wind_score']);
    }
}
