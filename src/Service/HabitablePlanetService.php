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

class HabitablePlanetService
{
    private ExoplanetApiFetcher $fetcher;

    public function __construct(ExoplanetApiFetcher $fetcher)
    {
        $this->fetcher = $fetcher;
    }

    /**
     * Determine if a planet is potentially habitable.
     *
     * @param array<string,mixed> $planet
     */
    private function isHabitable(array $planet): bool
    {
        $rade = isset($planet['pl_rade']) ? (float) $planet['pl_rade'] : null;
        $bmasse = isset($planet['pl_bmasse']) ? (float) $planet['pl_bmasse'] : null;
        $stTeff = isset($planet['st_teff']) ? (float) $planet['st_teff'] : null;
        $insol = isset($planet['pl_insol']) ? (float) $planet['pl_insol'] : null;

        // Fallback: approximate insolation from equilibrium temperature if missing
        if (null === $insol && isset($planet['pl_eqt'])) {
            $insol = ((float) $planet['pl_eqt'] / 255.0) ** 4;
        }

        // Reject planets missing essential data
        if (null === $rade || null === $stTeff || null === $insol) {
            return false;
        }

        // Habitable criteria
        $criteria = [
            'radius' => $rade >= 0.5 && $rade <= 3.0,
            'mass' => null === $bmasse || ($bmasse >= 0.1 && $bmasse <= 10.0),
            'insol' => $insol >= 0.2 && $insol <= 2.0,
            'st_teff' => $stTeff >= 3000 && $stTeff <= 7500,
        ];

        // Planet is habitable if all criteria pass
        return !\in_array(false, $criteria, true);
    }

    /**
     * Fetch planets and annotate them with habitability status.
     *
     * @return array<int,array<string,mixed>>
     *
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function fetchPlanetsWithHabitability(int $limit = 1000): array
    {
        $planets = $this->fetcher->fetch($limit);

        return array_map(function (array $planet) {
            $planet['habitable'] = $this->isHabitable($planet);

            return $planet;
        }, $planets);
    }
}
