<?php

/*
 * This file is part of the Symfony project.
 *
 * (c) Mujahid Ghadban
 *
 * For more information about routing and attributes, see:
 * https://symfony.com/doc/current/routing.html
 */

declare(strict_types=1);

namespace App\Controller;

use App\Service\PlanetStatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Controller used to display planetary discovery and type distribution statistics.
 *
 * This controller integrates with PlanetStatisticsService to render
 * planetary type distributions and discoveries by year.
 */
#[Route('/planets')]
final class PlanetStatisticsController extends AbstractController
{
    /**
     * @param PlanetStatisticsService $statsService
     */
    public function __construct(
        private readonly PlanetStatisticsService $statsService
    ) {
    }


    /**
     * @param string $_format
     * @return Response
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Route(
        path: '/statistics',
        name: 'planet_statistics',
        defaults: ['_format' => 'html'],
        methods: ['GET']
    )]
    #[Route(
        path: '/statistics.{_format}',
        name: 'planet_statistics_format',
        requirements: ['_format' => 'html|json'],
        methods: ['GET']
    )]
    public function index(string $_format = 'html'): Response
    {
        $distribution = $this->statsService->distributionByType();
        $byYear = $this->statsService->distributionByYear();

        $typeLabels = array_keys($distribution);
        $typeCounts = array_values($distribution);

        $years = array_keys($byYear);
        sort($years);

        // Collect all planet types for consistent order across years
        $allTypes = [];
        foreach ($byYear as $year => $map) {
            foreach (array_keys($map) as $type) {
                $allTypes[$type] = true;
            }
        }

        $allTypes = array_keys($allTypes);
        sort($allTypes);

        $yearLabels = [];
        $yearDatasets = [];
        foreach ($byYear as $year => $map) {
            $yearLabels[] = $year === 0 ? 'Unknown' : (string) $year;

            $row = [];
            foreach ($allTypes as $type) {
                $row[] = $map[$type] ?? 0;
            }

            $yearDatasets[] = $row;
        }

        // Render HTML or JSON depending on format
        if ($_format === 'json') {
            return $this->json([
                'typeLabels' => $typeLabels,
                'typeCounts' => $typeCounts,
                'yearLabels' => $yearLabels,
                'yearTypes' => $allTypes,
                'yearDatasets' => $yearDatasets,
            ]);
        }

        return $this->render('planet/statistics.html.twig', [
            'typeLabels' => $typeLabels,
            'typeCounts' => $typeCounts,
            'yearLabels' => $yearLabels,
            'yearTypes' => $allTypes,
            'yearDatasets' => $yearDatasets,
        ]);
    }
}
