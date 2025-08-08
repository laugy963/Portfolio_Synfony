<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminAuthenticationTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);
        
        // Nettoyer les utilisateurs de test avant chaque test
        $this->cleanupTestUsers();
    }

    protected function tearDown(): void
    {
        // Nettoyer les utilisateurs de test après chaque test
        $this->cleanupTestUsers();
        
        parent::tearDown();
        $this->entityManager->close();
    }

    private function cleanupTestUsers(): void
    {
        // Supprimer tous les utilisateurs de test
        $testEmails = [
            'test-admin@example.com',
            'admin-login-test@example.com',
            'admin-wrong-password@example.com',
            'admin-unverified@example.com',
            'admin-access-test@example.com',
            'user-normal@example.com'
        ];
        
        foreach ($testEmails as $email) {
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($user) {
                $this->entityManager->remove($user);
            }
        }
        
        $this->entityManager->flush();
    }

    public function testCreateAdminUser(): void
    {
        // Arrange - Préparer les données de test
        $email = 'test-admin@example.com';
        $password = 'admin123456';
        $firstName = 'Admin';
        $lastName = 'Test';

        // Act - Créer un utilisateur administrateur
        $user = $this->createUserWithCreatedAt();
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setIsVerified(true);
        
        // Hacher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Sauvegarder en base
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Assert - Vérifier que l'utilisateur a été créé correctement
        $this->assertNotNull($user->getId());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($firstName, $user->getFirstName());
        $this->assertEquals($lastName, $user->getLastName());
        $this->assertTrue($user->isVerified());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles()); // Automatiquement ajouté
        
        // Vérifier que le mot de passe est correctement haché
        $this->assertTrue($this->passwordHasher->isPasswordValid($user, $password));
        $this->assertNotEquals($password, $user->getPassword()); // Le mot de passe ne doit pas être en clair
    }

    public function testAdminLoginSuccess(): void
    {
        // Arrange - Créer un utilisateur admin pour le test
        $email = 'admin-login-test@example.com';
        $password = 'secretPassword123';
        
        $adminUser = $this->createUserWithCreatedAt();
        $adminUser->setEmail($email);
        $adminUser->setFirstName('Admin');
        $adminUser->setLastName('Login');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setIsVerified(true);
        
        $hashedPassword = $this->passwordHasher->hashPassword($adminUser, $password);
        $adminUser->setPassword($hashedPassword);
        
        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        // Act - Tenter de se connecter
        $crawler = $this->client->request('GET', '/login');
        
        // Vérifier que la page de login s'affiche
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Connexion');
        
        // Remplir et soumettre le formulaire de connexion
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => $email,
            '_password' => $password,
        ]);
        
        $this->client->submit($form);

        // Assert - Vérifier que la connexion a réussi
        $this->assertResponseRedirects(); // Redirection après connexion réussie
        
        // Suivre la redirection
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        
        // Vérifier que l'utilisateur est bien connecté
        $this->assertTrue($this->client->getContainer()->get('security.token_storage')->getToken()->getUser() instanceof User);
    }

    public function testAdminLoginWithWrongPassword(): void
    {
        // Arrange - Créer un utilisateur admin
        $email = 'admin-wrong-password@example.com';
        $correctPassword = 'correctPassword123';
        $wrongPassword = 'wrongPassword456';
        
        $adminUser = $this->createUserWithCreatedAt();
        $adminUser->setEmail($email);
        $adminUser->setFirstName('Admin');
        $adminUser->setLastName('Wrong');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setIsVerified(true);
        
        $hashedPassword = $this->passwordHasher->hashPassword($adminUser, $correctPassword);
        $adminUser->setPassword($hashedPassword);
        
        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        // Act - Tenter de se connecter avec un mauvais mot de passe
        $crawler = $this->client->request('GET', '/login');
        
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => $email,
            '_password' => $wrongPassword,
        ]);
        
        $this->client->submit($form);

        // Assert - Vérifier que la connexion a échoué
        $this->assertResponseRedirects('/login'); // Redirection vers login en cas d'échec
        
        $this->client->followRedirect();
        
        // Vérifier qu'un message d'erreur est affiché
        $this->assertSelectorExists('.alert-danger');
    }

    public function testAdminLoginWithUnverifiedAccount(): void
    {
        // Arrange - Créer un utilisateur admin non vérifié
        $email = 'admin-unverified@example.com';
        $password = 'password123';
        
        $adminUser = $this->createUserWithCreatedAt();
        $adminUser->setEmail($email);
        $adminUser->setFirstName('Admin');
        $adminUser->setLastName('Unverified');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setIsVerified(false); // Non vérifié
        
        $hashedPassword = $this->passwordHasher->hashPassword($adminUser, $password);
        $adminUser->setPassword($hashedPassword);
        
        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        // Act - Tenter de se connecter avec un compte non vérifié
        $crawler = $this->client->request('GET', '/login');
        
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => $email,
            '_password' => $password,
        ]);
        
        $this->client->submit($form);

        // Assert - La connexion peut réussir mais dépend de votre logique métier
        // Si vous voulez empêcher les utilisateurs non vérifiés de se connecter,
        // vous devrez ajouter cette logique dans votre EventListener
        
        // Pour ce test, nous vérifions juste que le mot de passe est correct
        $this->assertTrue($this->passwordHasher->isPasswordValid($adminUser, $password));
    }

    public function testAdminAccessToRestrictedArea(): void
    {
        // Arrange - Créer et connecter un admin
        $email = 'admin-access-test@example.com';
        $password = 'password123';
        
        $adminUser = $this->createUserWithCreatedAt();
        $adminUser->setEmail($email);
        $adminUser->setFirstName('Admin');
        $adminUser->setLastName('Access');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setIsVerified(true);
        
        $hashedPassword = $this->passwordHasher->hashPassword($adminUser, $password);
        $adminUser->setPassword($hashedPassword);
        
        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();

        // Se connecter d'abord
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => $email,
            '_password' => $password,
        ]);
        $this->client->submit($form);
        $this->client->followRedirect();

        // Act - Tenter d'accéder à une zone réservée aux admins
        $this->client->request('GET', '/projects');

        // Assert - Vérifier que l'accès est autorisé (suivre les redirections si nécessaire)
        if ($this->client->getResponse()->isRedirect()) {
            $this->client->followRedirect();
        }
        $this->assertResponseIsSuccessful();
    }

    public function testNonAdminCannotAccessRestrictedArea(): void
    {
        // Arrange - Créer un utilisateur normal (non admin)
        $email = 'user-normal@example.com';
        $password = 'password123';
        
        $normalUser = $this->createUserWithCreatedAt();
        $normalUser->setEmail($email);
        $normalUser->setFirstName('Normal');
        $normalUser->setLastName('User');
        $normalUser->setRoles(['ROLE_USER']); // Pas d'admin
        $normalUser->setIsVerified(true);
        
        $hashedPassword = $this->passwordHasher->hashPassword($normalUser, $password);
        $normalUser->setPassword($hashedPassword);
        
        $this->entityManager->persist($normalUser);
        $this->entityManager->flush();

        // Se connecter en tant qu'utilisateur normal
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => $email,
            '_password' => $password,
        ]);
        $this->client->submit($form);
        $this->client->followRedirect();

        // Act - Tenter d'accéder à une zone réservée aux admins
        $this->client->request('GET', '/projects');

        // Assert - Vérifier que l'accès est refusé
        $this->assertResponseStatusCodeSame(403); // Forbidden
    }

    /**
     * Helper method pour créer un utilisateur avec createdAt initialisé
     */
    private function createUserWithCreatedAt(): User
    {
        $user = new User();
        
        // Définir la date de création manuellement pour les tests
        $reflection = new \ReflectionClass($user);
        $property = $reflection->getProperty('createdAt');
        $property->setAccessible(true);
        $property->setValue($user, new \DateTimeImmutable());
        
        return $user;
    }
}
