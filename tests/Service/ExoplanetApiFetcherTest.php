<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\ExoplanetApiFetcher;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 *  Class ExoplanetApiFetcherTest
 */
class ExoplanetApiFetcherTest extends TestCase
{
    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function testFetchReturnsData(): void
    {
        // Mock the response returned by the HTTP client
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(200);
        $mockResponse->method('toArray')->willReturn([
            [
                'pl_name' => 'Planet A',
                'hostname' => 'Host 1',
                'pl_rade' => 1.0,
                'st_teff' => 5700,
            ],
            [
                'pl_name' => 'Planet B',
                'hostname' => 'Host 2',
                'pl_rade' => 2.0,
                'st_teff' => 5300,
            ],
        ]);

        // Mock the HttpClientInterface
        $mockClient = $this->createMock(HttpClientInterface::class);
        $mockClient->method('request')->willReturn($mockResponse);

        // Instantiate the service with the mocked client
        $fetcher = new ExoplanetApiFetcher($mockClient);

        $data = $fetcher->fetch(2);

        $this->assertCount(2, $data);
        $this->assertEquals('Planet A', $data[0]['pl_name']);
        $this->assertEquals('Planet B', $data[1]['pl_name']);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testFetchThrowsExceptionOnNon200(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getStatusCode')->willReturn(500);

        $mockClient = $this->createMock(HttpClientInterface::class);
        $mockClient->method('request')->willReturn($mockResponse);

        $fetcher = new ExoplanetApiFetcher($mockClient);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to fetch exoplanet data: 500');

        $fetcher->fetch(1);
    }
}
