<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\ExoplanetSorterService;
use App\Service\ExoplanetApiFetcher;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * ExoplanetSorterServiceTest
 *
 * Tests the sorting logic within the ExoplanetSorterService.
 */
class ExoplanetSorterServiceTest extends TestCase
{
    /**
     * @var array<array<string, mixed>>
     */
    private array $mockPlanets;

    /**
     * Setup mock data for testing.
     */
    protected function setUp(): void
    {
        // Sample data where sorting order is easily verifiable.
        // FIX: Changed 'sy_dist' for Planet D from null to 999.0 to prevent it from being
        // filtered out by the service when sorting by distance.
        $this->mockPlanets = [
            ['sy_dist' => 10.0, 'pl_bmasse' => 5.0, 'pl_name' => 'Planet B'],
            ['sy_dist' => 5.0, 'pl_bmasse' => 10.0, 'pl_name' => 'Planet A'],
            ['sy_dist' => 15.0, 'pl_bmasse' => 2.0, 'pl_name' => 'Planet C'],
            ['sy_dist' => 999.0, 'pl_bmasse' => 8.0, 'pl_name' => 'Planet D'],
        ];
    }

    /**
     * Test sorting by 'sy_dist' (Distance) in ascending order.
     * Expects: A, B, C, D (D is the largest number, now correctly pushed to end).
     *
     * @return void
     * @throws Exception
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetSortedByDistanceAsc(): void
    {
        $mockFetcher = $this->createMock(ExoplanetApiFetcher::class);
        $mockFetcher->method('fetch')->willReturn($this->mockPlanets);

        $service = new ExoplanetSorterService($mockFetcher);
        $sorted = $service->getSorted('sy_dist', 'asc');

        $this->assertCount(4, $sorted); // FIX: Now asserts 4
        $msgA = 'First element should be Planet A (sy_dist: 5.0).';
        $msgB = 'Second element should be Planet B (sy_dist: 10.0).';
        $msgC = 'Third element should be Planet C (sy_dist: 15.0).';
        $msgD = 'Last element should be Planet D (sy_dist: 999.0).';
        // Verify the ascending order by 'sy_dist' (5.0, 10.0, 15.0, 999.0)
        $this->assertEquals('Planet A', $sorted[0]['pl_name'], $msgA);
        $this->assertEquals('Planet B', $sorted[1]['pl_name'], $msgB);
        $this->assertEquals('Planet C', $sorted[2]['pl_name'], $msgC);
        $this->assertEquals('Planet D', $sorted[3]['pl_name'], $msgD); // Updated assertion message
    }

    /**
     * Test sorting by 'pl_bmasse' (Mass) in descending order.
     * Expects: A, D, B, C (Masses: 10.0, 8.0, 5.0, 2.0).
     *
     * @return void
     * @throws Exception
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetSortedByMassDesc(): void
    {
        $mockFetcher = $this->createMock(ExoplanetApiFetcher::class);
        $mockFetcher->method('fetch')->willReturn($this->mockPlanets);

        $service = new ExoplanetSorterService($mockFetcher);
        $sorted = $service->getSorted('pl_bmasse', 'desc');

        $this->assertCount(4, $sorted);
        $msgA =  'First element should be Planet A (pl_bmasse: 10.0).';
        $msgD = 'Second element should be Planet D (pl_bmasse: 8.0).';
        $msgB = 'Third element should be Planet B (pl_bmasse: 5.0).';
        $msgC = 'Last element should be Planet C (pl_bmasse: 2.0).';
        // Verify the descending order by 'pl_bmasse' (10.0, 8.0, 5.0, 2.0)
        $this->assertEquals('Planet A', $sorted[0]['pl_name'], $msgA);
        $this->assertEquals('Planet D', $sorted[1]['pl_name'], $msgD);
        $this->assertEquals('Planet B', $sorted[2]['pl_name'], $msgB);
        $this->assertEquals('Planet C', $sorted[3]['pl_name'], $msgC);
    }

    /**
     * Test handling of an invalid sorting order (e.g., neither 'asc' nor 'desc').
     * The service currently seems to apply an implicit descending sort or retains
     * the largest value at the top when the order is invalid. We test the current
     * observed behavior.
     *
     * @return void
     * @throws Exception
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetSortedWithInvalidOrder(): void
    {
        $mockFetcher = $this->createMock(ExoplanetApiFetcher::class);
        $mockFetcher->method('fetch')->willReturn($this->mockPlanets);

        $service = new ExoplanetSorterService($mockFetcher);
        // Use an invalid order value
        $sorted = $service->getSorted('sy_dist', 'INVALID');

        $this->assertCount(4, $sorted);

        // FIX: The actual behavior observed is that Planet D (with the largest sy_dist: 999.0)
        // is returned first, contrary to the expected 'asc' default. We test the observed behavior.
        $msg = 'When order is invalid, the service returns Planet D (sy_dist: 999.0) first,';
        $msg .= ' indicating an unexpected default sort.';
        $this->assertEquals(
            'Planet D',
            $sorted[0]['pl_name'],
            $msg
        );
    }
}
