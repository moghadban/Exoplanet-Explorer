<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\HabitablePlanetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class HabitablePlanetController.
 */
#[Route('/planets')]
final class HabitablePlanetController extends AbstractController
{
    /**
     * @param HabitablePlanetService $habitableService
     */
    public function __construct(
        private readonly HabitablePlanetService $habitableService
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
        path: '/habitable',
        name: 'planet_habitable',
        defaults: ['_format' => 'html'],
        methods: ['GET']
    )]
    #[Route(
        path: '/habitable.{_format}',
        name: 'planet_habitable_format',
        requirements: ['_format' => 'html|json'],
        methods: ['GET']
    )]
    public function index(string $_format = 'html'): Response
    {
        // Fetch planets with habitability annotations
        $planets = $this->habitableService->fetchPlanetsWithHabitability(1000);

        $habitable = [];
        $nonHabitable = [];
        foreach ($planets as $planet) {
            if ($planet['habitable']) {
                $habitable[] = $planet;
            } else {
                $nonHabitable[] = $planet;
            }
        }

        // Categorize planets by radius
        $radiusCategories = [
            'Small (<1 R⊕)' => 0,
            'Medium (1-2 R⊕)' => 0,
            'Large (>2 R⊕)' => 0,
        ];

        foreach ($planets as $planet) {
            $r = isset($planet['pl_rade']) ? (float)$planet['pl_rade'] : 0;
            if ($r < 1) {
                $radiusCategories['Small (<1 R⊕)']++;
            } elseif ($r <= 2) {
                $radiusCategories['Medium (1-2 R⊕)']++;
            } else {
                $radiusCategories['Large (>2 R⊕)']++;
            }
        }

        if ($_format === 'json') {
            return $this->json([
                'allPlanets' => $planets,
                'habitable' => $habitable,
                'nonHabitable' => $nonHabitable,
                'radiusCategories' => $radiusCategories,
            ]);
        }

        return $this->render('planet/habitable.html.twig', [
            'planets' => $planets,
            'habitable' => $habitable,
            'nonHabitable' => $nonHabitable,
            'radiusCategories' => $radiusCategories,
        ]);
    }
}
