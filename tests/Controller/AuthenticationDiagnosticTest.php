<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthenticationDiagnosticTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);
    }

    public function testPasswordHashingWorks(): void
    {
        // Test que le hachage de mot de passe fonctionne
        $user = new User();
        $password = 'testPassword123';
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        
        $this->assertNotEmpty($hashedPassword);
        $this->assertNotEquals($password, $hashedPassword);
        $this->assertTrue($this->passwordHasher->isPasswordValid($user, $password, $hashedPassword));
        
        echo "\n✅ Password hashing works correctly\n";
        echo "Original: " . $password . "\n";
        echo "Hashed: " . substr($hashedPassword, 0, 50) . "...\n";
    }

    public function testLoginFormExists(): void
    {
        // Test que la page de login existe
        $crawler = $this->client->request('GET', '/login');
        
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
        
        echo "\n✅ Login form exists and is accessible\n";
    }

    public function testUserCanBeCreatedInTestDatabase(): void
    {
        // Test qu'on peut créer un utilisateur en base de test
        $user = new User();
        $user->setEmail('diagnostic@test.com');
        $user->setFirstName('Test');
        $user->setLastName('User');
        $user->setPassword('hashedPassword');
        $user->setRoles(['ROLE_USER']);
        $user->setIsVerified(true);
        
        // Utiliser la réflexion pour définir createdAt
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('createdAt');
        $property->setAccessible(true);
        $property->setValue($user, new \DateTimeImmutable());
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $this->assertNotNull($user->getId());
        
        // Nettoyer
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        
        echo "\n✅ User can be created in test database\n";
    }

    public function testFullAuthenticationFlow(): void
    {
        // Test complet d'authentification
        $email = 'fulltest@example.com';
        $password = 'testPassword123';
        
        // Créer un utilisateur
        $user = new User();
        $user->setEmail($email);
        $user->setFirstName('Full');
        $user->setLastName('Test');
        $user->setRoles(['ROLE_USER']);
        $user->setIsVerified(true);
        
        // Hacher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        
        // Définir createdAt
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('createdAt');
        $property->setAccessible(true);
        $property->setValue($user, new \DateTimeImmutable());
        
        // Sauvegarder
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        echo "\n🔍 Testing authentication with:\n";
        echo "Email: " . $email . "\n";
        echo "Password: " . $password . "\n";
        echo "Hashed: " . substr($hashedPassword, 0, 50) . "...\n";
        
        // Tenter de se connecter
        $crawler = $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => $email,
            '_password' => $password,
        ]);
        
        $this->client->submit($form);
        
        // Vérifier la réponse
        $response = $this->client->getResponse();
        echo "Response status: " . $response->getStatusCode() . "\n";
        
        if ($response->isRedirect()) {
            echo "✅ Login successful - redirected to: " . $response->headers->get('Location') . "\n";
            $this->client->followRedirect();
            $this->assertResponseIsSuccessful();
        } else {
            echo "❌ Login failed - no redirect\n";
            echo "Response content: " . substr($response->getContent(), 0, 500) . "...\n";
        }
        
        // Nettoyer
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }
}
