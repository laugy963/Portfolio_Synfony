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
    name: 'app:test-login',
    description: 'Teste la connexion de l\'utilisateur admin',
)]
class TestLoginCommand extends Command
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

        $email = 'laukingstephane@gmail.com';
        $password = 'King97232/';

        $io->title('Test de connexion administrateur');
        $io->info('Email testé: ' . $email);

        try {
            // 1. Vérifier que l'utilisateur existe
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            
            if (!$user) {
                $io->error('❌ Utilisateur non trouvé avec l\'email: ' . $email);
                return Command::FAILURE;
            }

            $io->success('✅ Utilisateur trouvé');
            $io->table(
                ['Propriété', 'Valeur'],
                [
                    ['ID', $user->getId()],
                    ['Email', $user->getEmail()],
                    ['Prénom', $user->getFirstName()],
                    ['Nom', $user->getLastName()],
                    ['Rôles', implode(', ', $user->getRoles())],
                    ['Vérifié', $user->isVerified() ? 'Oui' : 'Non'],
                    ['Hash du mot de passe', substr($user->getPassword(), 0, 50) . '...'],
                ]
            );

            // 2. Tester le mot de passe
            $isPasswordValid = $this->passwordHasher->isPasswordValid($user, $password);
            
            if ($isPasswordValid) {
                $io->success('✅ Mot de passe correct');
            } else {
                $io->error('❌ Mot de passe incorrect');
                $io->note('Mot de passe testé: ' . $password);
                return Command::FAILURE;
            }

            // 3. Vérifier les rôles
            if (in_array('ROLE_ADMIN', $user->getRoles())) {
                $io->success('✅ Utilisateur a les droits d\'administrateur');
            } else {
                $io->warning('⚠️ Utilisateur n\'a pas les droits d\'administrateur');
            }

            // 4. Vérifier si le compte est vérifié
            if ($user->isVerified()) {
                $io->success('✅ Compte vérifié');
            } else {
                $io->warning('⚠️ Compte non vérifié');
            }

            $io->success('🎉 Tous les tests de connexion sont passés avec succès !');
            $io->note('Vous devriez pouvoir vous connecter avec ces identifiants.');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Erreur lors du test: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}