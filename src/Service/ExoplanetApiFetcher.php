<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class ExoplanetApiFetcher.
 */
class ExoplanetApiFetcher
{
    private HttpClientInterface $client;

    /**
     * private const API_URL = 'https://exoplanetarchive.ipac.caltech.edu/TAP/sync'.
     */
    private const API_URL = 'https://exoplanetarchive.ipac.caltech.edu/TAP/sync';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Fetch exoplanet data from NASA Exoplanet Archive via ADQL query.
     *
     * @param int $limit Number of records to fetch (default 1000)
     *
     * @return array<int,array<string,mixed>>
     *
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function fetch(int $limit = 1000): array
    {
        $query = <<<ADQL
                    SELECT TOP $limit
                    pl_name,
                    hostname,
                    discoverymethod,
                    disc_year,
                    pl_orbper,
                    pl_rade,
                    pl_bmasse,
                    pl_bmassj,
                    pl_eqt,
                    pl_insol,
                    st_teff,
                    st_rad,
                    st_mass,
                    sy_dist,
                    sy_vmag,
                    sy_gaiamag
                    FROM pscomppars -- Change 'ps' to 'pscomppars' here
                    WHERE pl_name IS NOT NULL AND pl_rade IS NOT NULL AND st_teff IS NOT NULL
                    ORDER BY disc_year DESC
                    ADQL;

        $response = $this->client->request('GET', self::API_URL, [
            'query' => [
                'query' => $query,
                'format' => 'json',
            ],
            'timeout' => 30,
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Failed to fetch exoplanet data: ' . $response->getStatusCode());
        }

        return $response->toArray();
    }
}
