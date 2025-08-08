<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationControllerTest extends WebTestCase
{
    private $client;
    private ?EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        
        // Nettoyer la base de données avant chaque test
        $this->cleanDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    private function cleanDatabase(): void
    {
        // Supprimer tous les utilisateurs de test
        $users = $this->entityManager->getRepository(User::class)->findAll();
        foreach ($users as $user) {
            if (str_contains($user->getEmail(), 'test') || str_contains($user->getEmail(), 'phpunit')) {
                $this->entityManager->remove($user);
            }
        }
        $this->entityManager->flush();
    }

    public function testRegistrationPageLoads(): void
    {
        $this->client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un compte');
    }

    public function testRegistrationFormSubmission(): void
    {
        $crawler = $this->client->request('GET', '/register');

        // Remplir le formulaire d'inscription
        $form = $crawler->selectButton('Créer mon compte')->form([
            'registration_form[firstName]' => 'John',
            'registration_form[lastName]' => 'Doe',
            'registration_form[email]' => 'phpunit.test@example.com',
            'registration_form[plainPassword][first]' => 'TestPassword123!',
            'registration_form[plainPassword][second]' => 'TestPassword123!',
            'registration_form[agreeTerms]' => true,
        ]);

        $this->client->submit($form);

        // Vérifier la redirection vers la page de vérification
        $this->assertResponseRedirects('/verify/code');

        // Vérifier que l'utilisateur a été créé en base
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'phpunit.test@example.com']);
        $this->assertNotNull($user);
        $this->assertEquals('John', $user->getFirstName());
        $this->assertEquals('Doe', $user->getLastName());
        $this->assertFalse($user->isVerified());
        $this->assertNotNull($user->getVerificationCode());
    }

    public function testRegistrationWithExistingEmail(): void
    {
        // Créer un utilisateur existant
        $existingUser = new User();
        $existingUser->setEmail('existing@example.com');
        $existingUser->setFirstName('Existing');
        $existingUser->setLastName('User');
        $existingUser->setRoles(['ROLE_USER']);
        $existingUser->setCreatedAt(new \DateTimeImmutable());
        $existingUser->setIsVerified(true);
        
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $existingUser->setPassword($passwordHasher->hashPassword($existingUser, 'password'));
        
        $this->entityManager->persist($existingUser);
        $this->entityManager->flush();

        // Essayer de s'inscrire avec le même email
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->selectButton('Créer mon compte')->form([
            'registration_form[firstName]' => 'John',
            'registration_form[lastName]' => 'Doe',
            'registration_form[email]' => 'existing@example.com',
            'registration_form[plainPassword][first]' => 'TestPassword123!',
            'registration_form[plainPassword][second]' => 'TestPassword123!',
            'registration_form[agreeTerms]' => true,
        ]);

        $this->client->submit($form);

        // Vérifier que l'erreur est affichée
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'Un compte avec cet email existe déjà');
    }

    public function testVerifyCodePageRequiresSession(): void
    {
        // Accéder à la page de vérification sans session
        $this->client->request('GET', '/verify/code');

        // Devrait rediriger vers l'inscription
        $this->assertResponseRedirects('/register');
    }

    public function testVerifyCodeWithValidCode(): void
    {
        // Créer un utilisateur avec un code de vérification
        $user = new User();
        $user->setEmail('verify.test@example.com');
        $user->setFirstName('Verify');
        $user->setLastName('Test');
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setIsVerified(false);
        $user->setVerificationCode('123456');
        $user->setVerificationCodeExpiresAt(new \DateTimeImmutable('+15 minutes'));
        
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Simuler la session avec l'email
        $session = $this->client->getContainer()->get('session');
        $session->set('verification_email', 'verify.test@example.com');

        // Accéder à la page de vérification
        $crawler = $this->client->request('GET', '/verify/code');
        $this->assertResponseIsSuccessful();

        // Soumettre le code de vérification
        $form = $crawler->selectButton('Vérifier')->form([
            'verification_code_form[email]' => 'verify.test@example.com',
            'verification_code_form[code]' => '123456',
        ]);

        $this->client->submit($form);

        // Vérifier la redirection vers la page de connexion
        $this->assertResponseRedirects('/login');

        // Vérifier que l'utilisateur est maintenant vérifié
        $this->entityManager->refresh($user);
        $this->assertTrue($user->isVerified());
        $this->assertNull($user->getVerificationCode());
    }

    public function testVerifyCodeWithInvalidCode(): void
    {
        // Créer un utilisateur avec un code de vérification
        $user = new User();
        $user->setEmail('invalid.test@example.com');
        $user->setFirstName('Invalid');
        $user->setLastName('Test');
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setIsVerified(false);
        $user->setVerificationCode('123456');
        $user->setVerificationCodeExpiresAt(new \DateTimeImmutable('+15 minutes'));
        
        $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Simuler la session avec l'email
        $session = $this->client->getContainer()->get('session');
        $session->set('verification_email', 'invalid.test@example.com');

        // Accéder à la page de vérification
        $crawler = $this->client->request('GET', '/verify/code');

        // Soumettre un code incorrect
        $form = $crawler->selectButton('Vérifier')->form([
            'verification_code_form[email]' => 'invalid.test@example.com',
            'verification_code_form[code]' => '654321',
        ]);

        $this->client->submit($form);

        // Vérifier que l'erreur est affichée
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert-danger', 'Code de vérification invalide');

        // Vérifier que l'utilisateur n'est pas vérifié
        $this->entityManager->refresh($user);
        $this->assertFalse($user->isVerified());
    }
}
