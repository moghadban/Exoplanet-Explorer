<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class ExoplanetSorterControllerTest
 *
 * Unit tests for the ExoplanetSorterController, verifying successful HTTP responses
 * and correct handling of HTML and JSON formats with different query parameters.
 */
final class ExoplanetSorterControllerTest extends WebTestCase
{
    /**
     * Test the default HTML rendering of the sorted exoplanets list.
     * (Defaults: criterion='sy_dist', order='asc')
     *
     * @return void
     */
    public function testIndexHtmlDefault(): void
    {
        $client = static::createClient();

        // Request the default HTML route
        $client->request('GET', 'en/exoplanets/sorted');

        $this->assertResponseIsSuccessful();
        // Since we don't have the Twig template, we only assert success.
        // A more advanced test would assert for a selector like 'table.exoplanet-table'
    }

    /**
     * Test the HTML rendering with explicit sorting parameters.
     *
     * @return void
     */
    public function testIndexHtmlWithParams(): void
    {
        $client = static::createClient();

        // Request HTML with custom criterion and order
        $client->request('GET', 'en/exoplanets/sorted?criterion=pl_name&order=desc');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test the default JSON output of the sorted exoplanets list.
     * (Defaults: criterion='sy_dist', order='asc')
     *
     * @return void
     */
    public function testIndexJsonDefault(): void
    {
        $client = static::createClient();

        // Request the JSON version using the 'format=json' query parameter
        $client->request('GET', 'en/exoplanets/sorted?format=json');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);

        // The controller returns an array of planets
        $this->assertIsArray($data);
    }

    /**
     * Test the JSON output with explicit sorting parameters.
     *
     * @return void
     */
    public function testIndexJsonWithParams(): void
    {
        $client = static::createClient();

        // Request the JSON version with custom criterion and order
        $client->request('GET', 'en/exoplanets/sorted?criterion=pl_name&order=desc&format=json');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');

        $data = json_decode($client->getResponse()->getContent(), true);

        // The controller returns an array of planets
        $this->assertIsArray($data);
    }
}
