<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:recreate-admin',
    description: 'Recrée l\'utilisateur administrateur avec les données du .env.local',
)]
class RecreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupérer les informations depuis les variables d'environnement
        $adminEmail = $_ENV['ADMIN_EMAIL'] ?? null;
        $adminFirstName = $_ENV['ADMIN_FIRSTNAME'] ?? null;
        $adminLastName = $_ENV['ADMIN_LASTNAME'] ?? null;
        $adminPassword = $_ENV['ADMIN_PASSWORD'] ?? null;

        // Vérifier que toutes les variables sont présentes
        if (!$adminEmail || !$adminFirstName || !$adminLastName || !$adminPassword) {
            $io->error('Les variables d\'environnement ADMIN_EMAIL, ADMIN_FIRSTNAME, ADMIN_LASTNAME et ADMIN_PASSWORD doivent être définies dans .env.local');
            return Command::FAILURE;
        }

        $io->title('Recréation de l\'utilisateur administrateur');
        $io->info('Email: ' . $adminEmail);
        $io->info('Nom: ' . $adminFirstName . ' ' . $adminLastName);

        try {
            // 1. Supprimer l'utilisateur existant s'il existe
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $adminEmail]);
            if ($existingUser) {
                $io->warning('Suppression de l\'utilisateur existant...');
                $this->entityManager->remove($existingUser);
                $this->entityManager->flush();
                $io->success('Utilisateur existant supprimé');
            }

            // 2. Créer le nouvel utilisateur admin
            $user = new User();
            $user->setEmail($adminEmail);
            $user->setFirstName($adminFirstName);
            $user->setLastName($adminLastName);
            $user->setRoles(['ROLE_ADMIN']);
            $user->setIsVerified(true);

            // Définir la date de création manuellement
            $reflection = new \ReflectionClass($user);
            $property = $reflection->getProperty('createdAt');
            $property->setAccessible(true);
            $property->setValue($user, new \DateTimeImmutable());

            // Hacher le mot de passe
            $hashedPassword = $this->passwordHasher->hashPassword($user, $adminPassword);
            $user->setPassword($hashedPassword);

            // Sauvegarder
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $io->success('✅ Utilisateur administrateur recréé avec succès !');
            $io->table(
                ['Propriété', 'Valeur'],
                [
                    ['ID', $user->getId()],
                    ['Email', $user->getEmail()],
                    ['Prénom', $user->getFirstName()],
                    ['Nom', $user->getLastName()],
                    ['Rôles', implode(', ', $user->getRoles())],
                    ['Vérifié', $user->isVerified() ? 'Oui' : 'Non'],
                    ['Date de création', $user->getCreatedAt()->format('Y-m-d H:i:s')],
                ]
            );

            // 3. Tester le mot de passe
            $isPasswordValid = $this->passwordHasher->isPasswordValid($user, $adminPassword);
            if ($isPasswordValid) {
                $io->success('✅ Mot de passe vérifié - hachage correct');
            } else {
                $io->error('❌ Problème avec le hachage du mot de passe');
                return Command::FAILURE;
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Erreur lors de la création de l\'utilisateur: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
