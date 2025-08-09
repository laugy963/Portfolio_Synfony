<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProjectControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/projects/');

        // La page projects/ nécessite une authentification, donc on s'attend à une redirection vers login
        self::assertResponseRedirects('/login');
    }
}
