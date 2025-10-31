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

namespace App\Controller;

use App\Service\ExoplanetSorterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class ExoplanetSorterController.
 */
class ExoplanetSorterController extends AbstractController
{
    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    #[Route('/exoplanets/sorted', name: 'exoplanet_sorted')]
    public function index(Request $request, ExoplanetSorterService $sorter): Response
    {
        $criterion = $request->query->get('criterion', 'sy_dist');
        $order = $request->query->get('order', 'asc');
        $planets = $sorter->getSorted($criterion, $order);

        // Handle JSON request (for the JS frontend)
        if ('json' === $request->query->get('format')) {
            return new JsonResponse($planets);
        }

        return $this->render('planet/sorted.html.twig', [
            'planets' => $planets,
            'criterion' => $criterion,
            'order' => $order,
        ]);
    }
}
