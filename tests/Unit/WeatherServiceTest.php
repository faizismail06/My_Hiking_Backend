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
}
