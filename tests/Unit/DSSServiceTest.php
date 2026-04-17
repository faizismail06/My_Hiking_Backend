<?php

namespace Tests\Unit;

use App\Models\Trail;
use App\Models\User;
use App\Services\DSSService;
use App\Services\WeatherService;
use Mockery;
use Tests\TestCase;

class DSSServiceTest extends TestCase
{
    public function test_evaluate_route_combines_route_and_weather_scores(): void
    {
        $weatherService = Mockery::mock(WeatherService::class);
        $weatherService
            ->shouldReceive('getCurrentWeather')
            ->once()
            ->andReturnUsing(function () {
                return [
                    'code' => 61,
                    'condition' => 'Rain',
                    'temperature' => 18.0,
                    'wind_speed' => 12.0,
                    'precipitation_probability' => 70,
                    'weather_score' => 3,
                    'wind_score' => 2,
                    'weather_score_final' => 2.7,
                ];
            });

        $service = new DSSService($weatherService);

        $user = new User([
            'tier' => 'pemula',
            'tier_source' => 'experience_onboarding',
            'level' => 1,
        ]);

        $trail = new Trail([
            'jarak' => 8,
            'elevasi' => 900,
            'durasi' => 7,
            'tingkat_kesulitan' => 'sedang',
            'latitude' => -7.242,
            'longitude' => 109.208,
        ]);

        $result = $service->evaluateRoute($user, $trail);

        $expectedRouteScore = round((0.8 * 0.3) + (0.9 * 0.4) + (0.7 * 0.3), 4);
        $expectedFinalScore = round(($expectedRouteScore * 0.75) + (2.7 * 0.25), 4);

        $this->assertEquals($expectedRouteScore, $result['route_score']);
        $this->assertEquals($expectedFinalScore, $result['final_score']);
        $this->assertSame(2.7, $result['weather_score_final']);
        $this->assertSame('Rain', $result['weather']['condition']);
        $this->assertNotEmpty($result['reasoning']);
    }

    public function test_risk_level_is_tier_aware_for_same_route_and_weather(): void
    {
        $weatherService = Mockery::mock(WeatherService::class);
        $weatherService
            ->shouldReceive('getCurrentWeather')
            ->twice()
            ->andReturnUsing(function () {
                return [
                    'code' => 1,
                    'condition' => 'Clear',
                    'temperature' => 17.0,
                    'wind_speed' => 5.0,
                    'precipitation_probability' => 10,
                    'weather_score' => 1,
                    'wind_score' => 1,
                    'weather_score_final' => 0.8,
                ];
            });

        $service = new DSSService($weatherService);

        $trail = new Trail([
            'jarak' => 8,
            'elevasi' => 900,
            'durasi' => 7,
            'tingkat_kesulitan' => 'sulit',
            'latitude' => -7.242,
            'longitude' => 109.208,
        ]);

        $tierPemula = new User([
            'tier' => 'pemula',
            'tier_source' => 'experience_onboarding',
            'level' => 1,
        ]);

        $tierMahir = new User([
            'tier' => 'mahir',
            'tier_source' => 'experience_onboarding',
            'level' => 1,
        ]);

        $resultPemula = $service->evaluateRoute($tierPemula, $trail);
        $resultMahir = $service->evaluateRoute($tierMahir, $trail);

        $this->assertSame('high_risk', $resultPemula['risk_level']);
        $this->assertSame('not_recommended', $resultPemula['recommendation']);

        $this->assertSame('safe', $resultMahir['risk_level']);
        $this->assertSame('recommended', $resultMahir['recommendation']);
        $this->assertGreaterThan($resultMahir['risk_gap'], $resultPemula['risk_gap']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
