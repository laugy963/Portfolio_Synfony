<?php

namespace App\Command;

use App\Entity\User;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test-verification',
    description: 'Test du système de vérification par email',
)]
class TestVerificationCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private EmailVerificationService $emailVerificationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EmailVerificationService $emailVerificationService
    ) {
        $this->entityManager = $entityManager;
        $this->emailVerificationService = $emailVerificationService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Adresse email pour le test')
            ->addArgument('firstName', InputArgument::OPTIONAL, 'Prénom', 'Test')
            ->addArgument('lastName', InputArgument::OPTIONAL, 'Nom', 'User')
            ->setHelp('Cette commande teste le système complet de vérification par email.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $emailAddress = $input->getArgument('email');
        $firstName = $input->getArgument('firstName');
        $lastName = $input->getArgument('lastName');

        $io->title('🔐 Test du système de vérification par email');

        try {
            // Créer un utilisateur de test temporaire
            $user = new User();
            $user->setEmail($emailAddress);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword('temp_password_for_test');
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setIsVerified(false);

            // Envoyer l'email de vérification
            $this->emailVerificationService->sendEmailConfirmation($user);

            $io->success([
                'Email de vérification envoyé avec succès !',
                'Destinataire: ' . $emailAddress,
                'Code généré et envoyé depuis: laukingportfolio@gmail.com'
            ]);

            $io->note([
                'Le code de vérification expire dans 15 minutes.',
                'Vérifiez votre boîte email pour récupérer le code à 6 chiffres.',
                'Code généré: ' . $user->getVerificationCode()
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error([
                'Erreur lors de l\'envoi de l\'email de vérification:',
                $e->getMessage()
            ]);

            return Command::FAILURE;
        }
    }
}
