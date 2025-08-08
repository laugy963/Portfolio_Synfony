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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:debug-registration',
    description: 'Debug complet du processus d\'inscription',
)]
class DebugRegistrationCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private EmailVerificationService $emailVerificationService;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        EmailVerificationService $emailVerificationService,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->emailVerificationService = $emailVerificationService;
        $this->passwordHasher = $passwordHasher;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Adresse email pour le test')
            ->addArgument('firstName', InputArgument::OPTIONAL, 'Prénom', 'Test')
            ->addArgument('lastName', InputArgument::OPTIONAL, 'Nom', 'User')
            ->setHelp('Cette commande simule complètement le processus d\'inscription.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $emailAddress = $input->getArgument('email');
        $firstName = $input->getArgument('firstName');
        $lastName = $input->getArgument('lastName');

        $io->title('🔍 Debug complet du processus d\'inscription');

        try {
            // Étape 1: Vérifier si l'utilisateur existe déjà
            $io->section('1. Vérification de l\'existence de l\'utilisateur');
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $emailAddress]);
            
            if ($existingUser) {
                $io->warning('Un utilisateur avec cet email existe déjà.');
                
                // Supprimer l'ancien utilisateur pour le test
                $io->note('Suppression de l\'ancien utilisateur pour le test...');
                $this->entityManager->remove($existingUser);
                $this->entityManager->flush();
                $io->success('Ancien utilisateur supprimé.');
            } else {
                $io->success('Aucun utilisateur existant trouvé.');
            }

            // Étape 2: Créer un nouvel utilisateur
            $io->section('2. Création du nouvel utilisateur');
            $user = new User();
            $user->setEmail($emailAddress);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setRoles(['ROLE_USER']);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setIsVerified(false);
            
            // Hasher le mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'TestPassword123!');
            $user->setPassword($hashedPassword);

            $io->info('Utilisateur créé en mémoire.');

            // Étape 3: Sauvegarder en base
            $io->section('3. Sauvegarde en base de données');
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $io->success('Utilisateur sauvegardé avec ID: ' . $user->getId());

            // Étape 4: Envoyer l'email de vérification
            $io->section('4. Envoi de l\'email de vérification');
            $this->emailVerificationService->sendEmailConfirmation($user);

            $io->success([
                'Processus d\'inscription simulé avec succès !',
                'Email: ' . $emailAddress,
                'Code de vérification: ' . $user->getVerificationCode(),
                'Expire à: ' . $user->getVerificationCodeExpiresAt()->format('Y-m-d H:i:s')
            ]);

            $io->note([
                'Vérifiez maintenant votre boîte email:',
                '- Expéditeur: laukingportfolio@gmail.com',
                '- Sujet: 🔐 Code de vérification - Portfolio',
                '- Code attendu: ' . $user->getVerificationCode()
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error([
                'Erreur lors du processus d\'inscription:',
                'Type: ' . get_class($e),
                'Message: ' . $e->getMessage(),
                'Fichier: ' . $e->getFile() . ':' . $e->getLine()
            ]);

            return Command::FAILURE;
        }
    }
}
