<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\EmailVerificationService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Mailer\MailerInterface;

class EmailVerificationServiceVerifyTest extends TestCase
{
    private EmailVerificationService $service;
    private MailerInterface&MockObject $mailerMock;
    private EntityManagerInterface&MockObject $entityManagerMock;
    private UserRepository&MockObject $userRepositoryMock;

    protected function setUp(): void
    {
        $this->mailerMock = $this->createMock(MailerInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);

        $this->service = new EmailVerificationService(
            $this->mailerMock,
            $this->entityManagerMock,
            'test@example.com',
            'Test Service'
        );
    }

    public function testVerifyUserEmailWithCodeSuccess(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setVerificationCode('123456');
        $user->setVerificationCodeExpiresAt(new \DateTimeImmutable('+15 minutes'));
        $user->setIsVerified(false);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'test@example.com'])
            ->willReturn($user);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($userRepository);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('flush');

        // Act
        $result = $this->service->verifyUserEmailWithCode('test@example.com', '123456');

        // Assert
        $this->assertTrue($result);
        $this->assertTrue($user->isVerified());
        $this->assertNull($user->getVerificationCode());
        $this->assertNull($user->getVerificationCodeExpiresAt());
    }

    public function testVerifyUserEmailWithCodeUserNotFound(): void
    {
        // Arrange
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'nonexistent@example.com'])
            ->willReturn(null);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($userRepository);

        $this->entityManagerMock
            ->expects($this->never())
            ->method('flush');

        // Act
        $result = $this->service->verifyUserEmailWithCode('nonexistent@example.com', '123456');

        // Assert
        $this->assertFalse($result);
    }

    public function testVerifyUserEmailWithCodeWrongCode(): void
    {
        // Arrange
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setVerificationCode('123456');
        $user->setVerificationCodeExpiresAt(new \DateTimeImmutable('+15 minutes'));
        $user->setIsVerified(false);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'test@example.com'])
            ->willReturn($user);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($userRepository);

        $this->entityManagerMock
            ->expects($this->never())
            ->method('flush');

        // Act
        $result = $this->service->verifyUserEmailWithCode('test@example.com', '654321');

        // Assert
        $this->assertFalse($result);
        $this->assertFalse($user->isVerified());
        $this->assertEquals('123456', $user->getVerificationCode());
    }

    public function testVerifyUserEmailWithCodeExpired(): void
    {
        // Créer un utilisateur avec un code expiré
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setVerificationCode('123456');
        $user->setVerificationCodeExpiresAt(new \DateTimeImmutable('-5 minutes')); // Expiré
        $user->setIsVerified(false);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'test@example.com'])
            ->willReturn($user);

        $this->entityManagerMock
            ->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($userRepository);

        $this->entityManagerMock
            ->expects($this->never())
            ->method('flush');

        $result = $this->service->verifyUserEmailWithCode('test@example.com', '123456');

        $this->assertFalse($result);
        $this->assertFalse($user->isVerified());
    }

    public function testResendVerificationCode(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setFirstName('John');

        // Configurer les mocks pour l'envoi du nouvel email
        $this->entityManagerMock->expects($this->once())->method('flush');
        $this->mailerMock->expects($this->once())->method('send');

        $this->service->resendVerificationCode($user);

        // Vérifier qu'un nouveau code a été généré
        $this->assertNotNull($user->getVerificationCode());
        $this->assertMatchesRegularExpression('/^\d{6}$/', $user->getVerificationCode());
    }
}
