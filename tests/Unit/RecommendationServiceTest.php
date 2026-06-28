<?php

namespace Tests\Unit;

use App\Services\RecommendationService;
use App\Services\TopsisService;
use App\Services\TopsisExplainerService;
use App\Services\DSSService;
use Tests\TestCase;

class RecommendationServiceTest extends TestCase
{
    public function test_recommendation_service_can_be_instantiated(): void
    {
        $topsisMock = $this->createMock(TopsisService::class);
        $explainerMock = $this->createMock(TopsisExplainerService::class);
        $dssMock = $this->createMock(DSSService::class);

        $service = new RecommendationService($topsisMock, $explainerMock, $dssMock);
        $this->assertInstanceOf(RecommendationService::class, $service);
    }
}
