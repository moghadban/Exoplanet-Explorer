<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class HabitablePlanetControllerTest
 */
class HabitablePlanetControllerTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testIndexHtmlResponse(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', 'en/planets/habitable');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('#habitablePie');
        $this->assertSelectorExists('#radiusBar');
    }

    /**
     * @return void
     */
    public function testIndexJsonResponse(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', 'en/planets/habitable.json');

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('allPlanets', $data);
        $this->assertArrayHasKey('habitable', $data);
        $this->assertArrayHasKey('nonHabitable', $data);
        $this->assertArrayHasKey('radiusCategories', $data);
    }
}
