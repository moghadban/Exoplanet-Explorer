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

/**
 * Class ExoplanetSorterService.
 */
class ExoplanetSorterService
{
    private ExoplanetApiFetcher $fetcher;

    public function __construct(ExoplanetApiFetcher $fetcher)
    {
        $this->fetcher = $fetcher;
    }

    /**
     * Fetch and sort exoplanets by a selected field.
     *
     * @param string $criterion e.g. 'sy_dist', 'pl_rade', 'disc_year'
     * @param string $order     'asc' or 'desc'
     *
     * @return array<mixed|string|int|float>
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getSorted(string $criterion = 'sy_dist', string $order = 'asc', int $limit = 200): array
    {
        $data = $this->fetcher->fetch($limit);

        // Only keep planets with numeric data for the chosen field
        $filtered = array_filter($data, fn ($p) => isset($p[$criterion]) && is_numeric($p[$criterion]));

        usort($filtered, function ($a, $b) use ($criterion, $order) {
            $valA = (float) $a[$criterion];
            $valB = (float) $b[$criterion];
            if ($valA === $valB) {
                return 0;
            }

            return 'asc' === $order ? ($valA <=> $valB) : ($valB <=> $valA);
        });

        return \array_slice($filtered, 0, $limit);
    }
}
