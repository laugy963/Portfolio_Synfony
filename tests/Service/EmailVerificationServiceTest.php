<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class EmailVerificationServiceTest extends TestCase
{
    private EmailVerificationService $emailVerificationService;
    private MailerInterface&MockObject $mailer;
    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        // Créer des mocks pour les dépendances
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Créer le service avec les mocks
        $this->emailVerificationService = new EmailVerificationService(
            $this->mailer,
            $this->entityManager,
            'test@example.com',
            'Test Service'
        );
    }

    public function testGenerateVerificationCode(): void
    {
        $code = $this->emailVerificationService->generateVerificationCode();

        // Vérifier que le code a 6 chiffres
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
        $this->assertEquals(6, strlen($code));
    }

    public function testGenerateVerificationCodeUniqueness(): void
    {
        $codes = [];
        
        // Générer 100 codes pour tester l'unicité
        for ($i = 0; $i < 100; $i++) {
            $codes[] = $this->emailVerificationService->generateVerificationCode();
        }

        // Vérifier qu'il y a au moins 90% de codes uniques (probabilité très élevée)
        $uniqueCodes = array_unique($codes);
        $this->assertGreaterThan(90, count($uniqueCodes));
    }

    public function testSendEmailConfirmation(): void
    {
        // Créer un utilisateur de test
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setFirstName('John');
        $user->setLastName('Doe');

        // Configurer le mock de l'EntityManager
        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Configurer le mock du Mailer pour vérifier l'envoi
        $this->mailer
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function ($email) {
                return $email instanceof TemplatedEmail &&
                       $email->getTo()[0]->getAddress() === 'test@example.com' &&
                       $email->getSubject() === '🔐 Code de vérification - Portfolio';
            }));

        // Appeler la méthode
        $this->emailVerificationService->sendEmailConfirmation($user);

        // Vérifier que le code de vérification a été défini
        $this->assertNotNull($user->getVerificationCode());
        $this->assertMatchesRegularExpression('/^\d{6}$/', $user->getVerificationCode());

        // Vérifier que la date d'expiration a été définie
        $this->assertNotNull($user->getVerificationCodeExpiresAt());
        $this->assertGreaterThan(new \DateTimeImmutable(), $user->getVerificationCodeExpiresAt());
    }

    public function testSendEmailConfirmationCodeExpiration(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->entityManager->expects($this->once())->method('flush');
        $this->mailer->expects($this->once())->method('send');

        $beforeSend = new \DateTimeImmutable();
        $this->emailVerificationService->sendEmailConfirmation($user);
        $afterSend = new \DateTimeImmutable('+16 minutes'); // Un peu plus que 15 minutes

        // Vérifier que l'expiration est d'environ 15 minutes
        $this->assertLessThan($afterSend, $user->getVerificationCodeExpiresAt());
        $this->assertGreaterThan($beforeSend->modify('+14 minutes'), $user->getVerificationCodeExpiresAt());
    }
}
