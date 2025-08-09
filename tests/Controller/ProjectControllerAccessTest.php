<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProjectControllerAccessTest extends WebTestCase
{
    private const TEST_EMAILS = [
        'admin' => 'test.admin@example.com',
        'user' => 'test.user@example.com'
    ];

    protected function setUp(): void
    {
        $this->cleanupTestUsers();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestUsers();
    }

    private function createUserInDatabase(string $email, string $password, array $roles = ['ROLE_USER']): User
    {
        $kernel = self::bootKernel();
        
        try {
            $entityManager = $kernel->getContainer()->get('doctrine')->getManager();
            $passwordHasher = $kernel->getContainer()->get(UserPasswordHasherInterface::class);
            
            $user = new User();
            $user->setEmail($email);
            $user->setRoles($roles);
            $user->setPassword($passwordHasher->hashPassword($user, $password));
            $user->setIsVerified(true);
            
            // Définir created_at avec réflexion
            $reflection = new \ReflectionClass($user);
            $property = $reflection->getProperty('createdAt');
            $property->setAccessible(true);
            $property->setValue($user, new \DateTimeImmutable());
            
            $entityManager->persist($user);
            $entityManager->flush();
            
            // Récupérer l'ID avant de détacher
            $userId = $user->getId();
            $entityManager->detach($user);
            
            return $user;
        } finally {
            $kernel->shutdown();
        }
    }

    private function cleanupTestUsers(): void
    {
        $kernel = self::bootKernel();
        
        try {
            $entityManager = $kernel->getContainer()->get('doctrine')->getManager();
            
            foreach (self::TEST_EMAILS as $email) {
                $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                if ($user) {
                    $entityManager->remove($user);
                }
            }
            $entityManager->flush();
        } catch (\Exception $e) {
            // Ignorer les erreurs de nettoyage en mode test
        } finally {
            $kernel->shutdown();
        }
    }

    public function testProjectIndexAccessDeniedForUnauthenticatedUser(): void
    {
        $client = static::createClient();
        $client->request('GET', '/project');
        
        $this->assertResponseRedirects();
        $this->assertResponseStatusCodeSame(302);
    }

    public function testProjectIndexAccessDeniedForRegularUser(): void
    {
        $user = $this->createUserInDatabase(self::TEST_EMAILS['user'], 'password123', ['ROLE_USER']);
        
        $client = static::createClient();
        $client->loginUser($user);
        $client->request('GET', '/project');
        
        $this->assertResponseStatusCodeSame(403);
    }

    public function testProjectIndexAccessAllowedForAdmin(): void
    {
        $admin = $this->createUserInDatabase(self::TEST_EMAILS['admin'], 'password123', ['ROLE_ADMIN']);
        
        $client = static::createClient();
        $client->loginUser($admin);
        $client->request('GET', '/project');
        
        $this->assertResponseIsSuccessful();
    }

    public function testNavbarGestionButtonVisibilityForAdmin(): void
    {
        $admin = $this->createUserInDatabase(self::TEST_EMAILS['admin'], 'password123', ['ROLE_ADMIN']);
        
        $client = static::createClient();
        $client->loginUser($admin);
        $crawler = $client->request('GET', '/');
        
        $this->assertSelectorExists('a[href="/project"]');
    }

    public function testNavbarGestionButtonHiddenForRegularUser(): void
    {
        $user = $this->createUserInDatabase(self::TEST_EMAILS['user'], 'password123', ['ROLE_USER']);
        
        $client = static::createClient();
        $client->loginUser($user);
        $crawler = $client->request('GET', '/');
        
        $this->assertSelectorNotExists('a[href="/project"]');
    }
}
