<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\HabitablePlanetService;
use App\Service\ExoplanetApiFetcher;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class HabitablePlanetServiceTest
 */
class HabitablePlanetServiceTest extends TestCase
{
    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws Exception
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testFetchPlanetsWithHabitability(): void
    {
        $mockFetcher = $this->createMock(ExoplanetApiFetcher::class);

        $mockFetcher->method('fetch')->with(3)->willReturn([
            // Habitable planet: radius, mass, insolation, temp within criteria
            ['pl_rade' => 1.0, 'pl_bmasse' => 1.0, 'pl_insol' => 1.0, 'st_teff' => 5500],

            // Non-habitable: radius too large
            ['pl_rade' => 5.0, 'pl_bmasse' => 1.0, 'pl_insol' => 1.0, 'st_teff' => 5500],

            // Non-habitable: missing essential data (st_teff)
            ['pl_rade' => 1.0, 'pl_bmasse' => 1.0, 'pl_insol' => 1.0],
        ]);

        $service = new HabitablePlanetService($mockFetcher);

        $planets = $service->fetchPlanetsWithHabitability(3);

        $this->assertCount(3, $planets);

        // First planet should be habitable
        $this->assertTrue($planets[0]['habitable']);

        // Second planet: radius too large => not habitable
        $this->assertFalse($planets[1]['habitable']);

        // Third planet: missing st_teff => not habitable
        $this->assertFalse($planets[2]['habitable']);
    }


    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function testFetchPlanetsWithEqTempFallback(): void
    {
        $mockFetcher = $this->createMock(ExoplanetApiFetcher::class);

        $mockFetcher->method('fetch')->willReturn([
            ['pl_rade' => 1.0, 'pl_bmasse' => 1.0, 'pl_eqt' => 255.0, 'st_teff' => 5500],
        ]);

        $service = new HabitablePlanetService($mockFetcher);

        $planets = $service->fetchPlanetsWithHabitability(1);

        $this->assertCount(1, $planets);
        $this->assertTrue($planets[0]['habitable']);
    }
}
