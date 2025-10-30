<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class PlanetStatisticsService
{
    /**
     * @var ExoplanetApiFetcher
     */
    private ExoplanetApiFetcher $fetcher;

    /**
     * @param ExoplanetApiFetcher $fetcher
     */
    public function __construct(ExoplanetApiFetcher $fetcher)
    {
        $this->fetcher = $fetcher;
    }

    /**
     * @param array<float> $row
     * @return string
     */
    public function categorize(array $row): string
    {
        $radius = $this->valueAsFloat($row, 'pl_rade');
        $massJ = $this->valueAsFloat($row, 'pl_bmassj');
        $massE = $this->valueAsFloat($row, 'pl_bmasse');

        if ($massE !== null && $massJ === null) {
            $massJ = $massE / 317.8; // Earth → Jupiter
        }

        if ($massJ !== null && $massJ >= 0.1) {
            return 'Gas giant';
        }

        if ($radius !== null) {
            if ($radius >= 6.0) {
                return 'Gas giant';
            }
            if ($radius >= 2.5 && $radius < 6.0) {
                return 'Neptune-like';
            }
            if ($radius > 1.25 && $radius < 2.5) {
                return 'Super-Earth';
            }
            if ($radius <= 1.25) {
                return 'Terrestrial';
            }
        }

        if ($massJ !== null) {
            if ($massJ >= 0.02 && $massJ < 0.1) {
                return 'Neptune-like';
            }
            if ($massJ < 0.02) {
                return 'Super-Earth / Terrestrial';
            }
        }

        return 'Unknown';
    }

    /**
     * @return array<int>
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function distributionByType(): array
    {
        $counts = [];
        foreach ($this->fetcher->fetch() as $row) {
            $type = $this->categorize($row);
            $counts[$type] = ($counts[$type] ?? 0) + 1;
        }
        ksort($counts);
        return $counts;
    }

    /**
     * @return array<int, array<string, int>>
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function distributionByYear(): array
    {
        $byYear = [];
        foreach ($this->fetcher->fetch() as $row) {
            $type = $this->categorize($row);
            $year = $this->valueAsInt($row, 'disc_year') ?? 0;
            $byYear[$year][$type] = ($byYear[$year][$type] ?? 0) + 1;
        }
        ksort($byYear);
        return $byYear;
    }

    /**
     * @param array<mixed|float|string> $row
     * @param string $key
     * @return float|null
     */
    private function valueAsFloat(array $row, string $key): ?float
    {
        if (!isset($row[$key])) {
            return null;
        }
        $raw = str_replace(',', '', (string)$row[$key]);
        return is_numeric($raw) ? (float)$raw : null;
    }

    /**
     * @param array<mixed|float|string> $row
     * @param string $key
     * @return int|null
     */
    private function valueAsInt(array $row, string $key): ?int
    {
        if (!isset($row[$key])) {
            return null;
        }
        $raw = trim((string)$row[$key]);
        return is_numeric($raw) ? (int)floor((float)$raw) : null;
    }
}
