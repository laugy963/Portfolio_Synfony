<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;

class EmailVerificationService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly EntityManagerInterface $entityManager,
        private readonly AppEmailFactory $emailFactory,
    ) {
    }

    public function generateVerificationCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function sendEmailConfirmation(User $user): void
    {
        // Générer un code de vérification à 6 chiffres
        $verificationCode = $this->generateVerificationCode();
        
        // Définir la date d'expiration (15 minutes)
        $expiresAt = new \DateTimeImmutable('+15 minutes');
        
        // Sauvegarder le code en base
        $user->setVerificationCode($verificationCode);
        $user->setVerificationCodeExpiresAt($expiresAt);
        $this->entityManager->flush();

        // Envoyer l'email avec le code
        $email = $this->emailFactory
            ->createTemplatedEmail('Code de verification - Portfolio')
            ->to($user->getEmail())
            ->htmlTemplate('emails/verification_code.html.twig')
            ->context([
                'user' => $user,
                'verificationCode' => $verificationCode,
                'expiresAt' => $expiresAt,
                'fromAddress' => $this->emailFactory->getFromAddress(),
            ]);

        $this->mailer->send($email);
    }

    public function verifyUserEmailWithCode(string $email, string $code): bool
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        
        if (!$user) {
            return false;
        }

        // Vérifier si le code correspond
        if ($user->getVerificationCode() !== $code) {
            return false;
        }

        // Vérifier si le code n'a pas expiré
        if ($user->isVerificationCodeExpired()) {
            return false;
        }

        // Marquer l'utilisateur comme vérifié
        $user->setIsVerified(true);
        $user->setVerificationCode(null);
        $user->setVerificationCodeExpiresAt(null);
        $this->entityManager->flush();

        return true;
    }

    public function resendVerificationCode(User $user): void
    {
        // Regénérer un nouveau code
        $this->sendEmailConfirmation($user);
    }
}
