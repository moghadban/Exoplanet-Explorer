<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\PlanetStatisticsService;
use App\Service\ExoplanetApiFetcher;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * PlanetStatisticsServiceTest
 */
class PlanetStatisticsServiceTest extends TestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testCategorize(): void
    {
        $mockFetcher = $this->createMock(ExoplanetApiFetcher::class);
        $service = new PlanetStatisticsService($mockFetcher);

        // Terrestrial
        $this->assertEquals(
            'Terrestrial',
            $service->categorize(['pl_rade' => 1.0])
        );

        // Super-Earth
        $this->assertEquals(
            'Super-Earth',
            $service->categorize(['pl_rade' => 1.5])
        );

        // Neptune-like
        $this->assertEquals(
            'Neptune-like',
            $service->categorize(['pl_rade' => 3.0])
        );

        // Gas giant
        $this->assertEquals(
            'Gas giant',
            $service->categorize(['pl_rade' => 6.5])
        );

        // Gas giant by mass
        $this->assertEquals(
            'Gas giant',
            $service->categorize(['pl_bmassj' => 0.2])
        );

        // Super-Earth / Terrestrial by low mass
        $this->assertEquals(
            'Super-Earth / Terrestrial',
            $service->categorize(['pl_bmassj' => 0.01])
        );

        // Unknown
        $this->assertEquals(
            'Unknown',
            $service->categorize([])
        );
    }

    /**
     * @return void
     * @throws Exception
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testDistributionByType(): void
    {
        $mockFetcher = $this->createMock(ExoplanetApiFetcher::class);

        $mockFetcher->method('fetch')->willReturn([
            ['pl_rade' => 1.0], // Terrestrial
            ['pl_rade' => 1.5], // Super-Earth
            ['pl_rade' => 3.0], // Neptune-like
            ['pl_rade' => 6.5], // Gas giant
            ['pl_rade' => 6.1], // Gas giant
        ]);

        $service = new PlanetStatisticsService($mockFetcher);
        $distribution = $service->distributionByType();

        $expected = [
            'Gas giant' => 2,
            'Neptune-like' => 1,
            'Super-Earth' => 1,
            'Terrestrial' => 1,
        ];

        $this->assertEquals($expected, $distribution);
    }

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws Exception
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testDistributionByYear(): void
    {
        $mockFetcher = $this->createMock(ExoplanetApiFetcher::class);

        $mockFetcher->method('fetch')->willReturn([
            ['pl_rade' => 1.0, 'disc_year' => 2020], // Terrestrial
            ['pl_rade' => 1.5, 'disc_year' => 2020], // Super-Earth
            ['pl_rade' => 3.0, 'disc_year' => 2021], // Neptune-like
            ['pl_rade' => 6.5, 'disc_year' => null], // Gas giant, unknown year
        ]);

        $service = new PlanetStatisticsService($mockFetcher);
        $byYear = $service->distributionByYear();

        $expected = [
            0 => ['Gas giant' => 1],
            2020 => ['Super-Earth' => 1, 'Terrestrial' => 1],
            2021 => ['Neptune-like' => 1],
        ];

        $this->assertEquals($expected, $byYear);
    }
}
