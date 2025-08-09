<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProjectControllerAccessTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = $kernel->getContainer()->get(UserPasswordHasherInterface::class);
    }

    private function createUserWithCreatedAt(string $email, string $password, array $roles = ['ROLE_USER']): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setIsVerified(true);
        
        // Utiliser la réflexion pour définir created_at même si c'est private/protected
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('createdAt');
        $property->setAccessible(true);
        $property->setValue($user, new \DateTimeImmutable());
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $user;
    }

    private function cleanupTestUsers(): void
    {
        // Supprimer les utilisateurs de test
        $testEmails = [
            'test.admin@example.com',
            'test.user@example.com'
        ];
        
        foreach ($testEmails as $email) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($user) {
                $this->entityManager->remove($user);
            }
        }
        $this->entityManager->flush();
    }

    public function testProjectIndexAccessDeniedForUnauthenticatedUser(): void
    {
        $client = static::createClient();
        
        // Tentative d'accès sans authentification
        $client->request('GET', '/projects/');
        
        // Doit être redirigé vers la page de connexion
        $this->assertResponseRedirects('/login');
    }

    public function testProjectIndexAccessDeniedForRegularUser(): void
    {
        $this->cleanupTestUsers();
        
        $client = static::createClient();
        
        // Créer un utilisateur normal
        $user = $this->createUserWithCreatedAt('test.user@example.com', 'password123', ['ROLE_USER']);
        
        // Se connecter avec l'utilisateur normal
        $client->loginUser($user);
        
        // Tentative d'accès à la gestion des projets
        $client->request('GET', '/projects/');
        
        // Doit recevoir un 403 Forbidden
        $this->assertResponseStatusCodeSame(403);
        
        $this->cleanupTestUsers();
    }

    public function testProjectIndexAccessAllowedForAdmin(): void
    {
        $this->cleanupTestUsers();
        
        $client = static::createClient();
        
        // Créer un utilisateur administrateur
        $admin = $this->createUserWithCreatedAt('test.admin@example.com', 'password123', ['ROLE_ADMIN', 'ROLE_USER']);
        
        // Se connecter avec l'administrateur
        $client->loginUser($admin);
        
        // Accès à la gestion des projets
        $client->request('GET', '/projects/');
        
        // Doit réussir
        $this->assertResponseIsSuccessful();
        
        $this->cleanupTestUsers();
    }

    public function testNavbarGestionButtonVisibilityForAdmin(): void
    {
        $this->cleanupTestUsers();
        
        $client = static::createClient();
        
        // Créer un utilisateur administrateur
        $admin = $this->createUserWithCreatedAt('test.admin@example.com', 'password123', ['ROLE_ADMIN', 'ROLE_USER']);
        
        // Se connecter avec l'administrateur
        $client->loginUser($admin);
        
        // Aller sur la page d'accueil
        $crawler = $client->request('GET', '/');
        
        // Vérifier que le bouton Gestion est présent
        $this->assertStringContainsString('Gestion', $crawler->text());
        $this->assertGreaterThan(0, $crawler->filter('a[href*="/projects/"]')->count());
        
        $this->cleanupTestUsers();
    }

    public function testNavbarGestionButtonHiddenForRegularUser(): void
    {
        $this->cleanupTestUsers();
        
        $client = static::createClient();
        
        // Créer un utilisateur normal
        $user = $this->createUserWithCreatedAt('test.user@example.com', 'password123', ['ROLE_USER']);
        
        // Se connecter avec l'utilisateur normal
        $client->loginUser($user);
        
        // Aller sur la page d'accueil
        $crawler = $client->request('GET', '/');
        
        // Vérifier que le bouton Gestion n'est PAS présent
        $this->assertEquals(0, $crawler->filter('a[href*="/projects/"]:contains("Gestion")')->count());
        
        // Mais vérifier que le bouton Déconnexion est présent (pour confirmer que l'utilisateur est connecté)
        $this->assertStringContainsString('Déconnexion', $crawler->text());
        
        $this->cleanupTestUsers();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestUsers();
        parent::tearDown();
        $this->entityManager->close();
    }
}
