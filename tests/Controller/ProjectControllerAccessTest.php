<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectControllerAccessTest extends WebTestCase
{
    public function testProjectIndexAccessDeniedForUnauthenticatedUser(): void
    {
        $client = static::createClient();
        $client->request('GET', '/projects/');
        
        $this->assertResponseRedirects();
        $this->assertResponseStatusCodeSame(302);
    }

    public function testProjectIndexRequiresAuthentication(): void
    {
        $client = static::createClient();
        
        // Test que l'accès direct à /projects/ sans auth redirige vers login
        $crawler = $client->request('GET', '/projects/');
        $this->assertResponseRedirects();
        
        // Vérifier que c'est bien une redirection vers la page de login
        $location = $client->getResponse()->headers->get('Location');
        $this->assertStringContainsString('/login', $location);
    }

    public function testProjectRouteExists(): void
    {
        // Test simple que la route existe au niveau routing 
        $client = static::createClient();
        
        // On teste avec une requête GET sur /projects/
        $client->request('GET', '/projects/');
        
        // La route existe (pas de 404), mais on est redirigé car pas authentifié
        $this->assertNotEquals(404, $client->getResponse()->getStatusCode(), 
            'La route /projects/ doit exister (ne pas retourner 404)');
    }
}
