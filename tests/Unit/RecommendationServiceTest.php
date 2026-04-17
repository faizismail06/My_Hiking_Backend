<?php

namespace Tests\Unit;

use App\Services\RecommendationService;
use App\Services\TopsisService;
use App\Services\WeatherService;
use ReflectionMethod;
use Tests\TestCase;

class RecommendationServiceTest extends TestCase
{
    public function test_mahir_below_unlock_threshold_excludes_sangat_sulit(): void
    {
        $service = $this->makeService();
        $alternatives = $this->sampleAlternatives();

        $selected = $this->invokeTierSelection($service, $alternatives, 3, 2);

        $this->assertCount(1, $selected);
        $this->assertSame('sulit', $selected[0]['overall_difficulty']);
    }

    public function test_mahir_at_unlock_threshold_allows_sangat_sulit(): void
    {
        $service = $this->makeService();
        $alternatives = $this->sampleAlternatives();

        $selected = $this->invokeTierSelection($service, $alternatives, 3, 3);
        $selectedLabels = array_values(array_unique(array_map(fn (array $item) => (string) $item['overall_difficulty'], $selected)));

        $this->assertContains('sulit', $selectedLabels);
        $this->assertContains('sangat_sulit', $selectedLabels);
        $this->assertNotContains('sedang', $selectedLabels);
    }

    public function test_menengah_never_unlocks_sangat_sulit_even_with_high_completed_hikes(): void
    {
        $service = $this->makeService();
        $alternatives = $this->sampleAlternatives();

        $selected = $this->invokeTierSelection($service, $alternatives, 2, 99);

        $this->assertCount(1, $selected);
        $this->assertSame('sedang', $selected[0]['overall_difficulty']);
    }

    /**
     * @param array<int, array<string, mixed>> $alternatives
     * @return array<int, array<string, mixed>>
     */
    private function invokeTierSelection(RecommendationService $service, array $alternatives, int $tierLevel, int $completedHikes): array
    {
        $method = new ReflectionMethod(RecommendationService::class, 'selectAlternativesByTierPolicy');
        $method->setAccessible(true);

        /** @var array<int, array<string, mixed>> $result */
        $result = $method->invoke($service, $alternatives, $tierLevel, $completedHikes);

        return $result;
    }

    private function makeService(): RecommendationService
    {
        return new RecommendationService(new WeatherService(), new TopsisService());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function sampleAlternatives(): array
    {
        return [
            [
                'route_id' => 1,
                'overall_difficulty' => 'sedang',
                'criteria' => [],
            ],
            [
                'route_id' => 2,
                'overall_difficulty' => 'sulit',
                'criteria' => [],
            ],
            [
                'route_id' => 3,
                'overall_difficulty' => 'sangat_sulit',
                'criteria' => [],
            ],
        ];
    }
}
