<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class PlanetStatisticsControllerTest
 */
final class PlanetStatisticsControllerTest extends WebTestCase
{
    /**
     * @return void
     */
    public function testIndexHtml(): void
    {
        $client = static::createClient();

        // Request the HTML version of the statistics page
        $crawler = $client->request('GET', 'en/planets/statistics');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('canvas#typePie', 'Type distribution chart exists');
        $this->assertSelectorExists('canvas#byYearStacked', 'Yearly distribution chart exists');
    }

    /**
     * @return void
     */
    public function testIndexJson(): void
    {
        $client = static::createClient();

        // Request the JSON version
        $client->request('GET', 'en/planets/statistics.json');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('typeLabels', $data);
        $this->assertArrayHasKey('typeCounts', $data);
        $this->assertArrayHasKey('yearLabels', $data);
        $this->assertArrayHasKey('yearTypes', $data);
        $this->assertArrayHasKey('yearDatasets', $data);

        // Optional: verify arrays are not empty
        $this->assertNotEmpty($data['typeLabels']);
        $this->assertNotEmpty($data['yearLabels']);
    }

    /**
     * @return void
     */
    public function testJsonConsistency(): void
    {
        $client = static::createClient();

        $client->request('GET', 'en/planets/statistics.json');
        $data = json_decode($client->getResponse()->getContent(), true);

        // Ensure typeCounts matches typeLabels count
        $this->assertCount(count($data['typeLabels']), $data['typeCounts']);

        // Ensure yearDatasets rows match yearLabels count
        $this->assertCount(count($data['yearLabels']), $data['yearDatasets']);
    }
}
