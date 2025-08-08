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
    name: 'app:debug-auth',
    description: 'Diagnostique complet du système d\'authentification',
)]
class DebugAuthCommand extends Command
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

        $io->title('🔍 Diagnostic du système d\'authentification');

        try {
            // 1. Vérifier les utilisateurs
            $users = $this->entityManager->getRepository(User::class)->findAll();
            
            $io->section('👥 Utilisateurs en base');
            $tableData = [];
            foreach ($users as $user) {
                $tableData[] = [
                    $user->getId(),
                    $user->getEmail(),
                    $user->getFirstName() . ' ' . $user->getLastName(),
                    implode(', ', $user->getRoles()),
                    $user->isVerified() ? '✅ Vérifié' : '❌ Non vérifié',
                    $user->getCreatedAt() ? $user->getCreatedAt()->format('Y-m-d H:i') : 'N/A'
                ];
            }
            
            $io->table(['ID', 'Email', 'Nom', 'Rôles', 'Statut', 'Créé le'], $tableData);

            // 2. Test spécifique de l'admin
            $adminEmail = 'laukingstephane@gmail.com';
            $adminPassword = 'King97232/';
            
            $io->section('🔐 Test de l\'utilisateur admin');
            
            $adminUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $adminEmail]);
            
            if (!$adminUser) {
                $io->error('❌ Utilisateur admin non trouvé');
                return Command::FAILURE;
            }

            $io->info('✅ Utilisateur admin trouvé : ' . $adminUser->getEmail());
            
            // Test du mot de passe
            $isPasswordValid = $this->passwordHasher->isPasswordValid($adminUser, $adminPassword);
            
            if ($isPasswordValid) {
                $io->success('✅ Mot de passe admin correct');
            } else {
                $io->error('❌ Mot de passe admin incorrect');
                $io->warning('Hash stocké : ' . substr($adminUser->getPassword(), 0, 50) . '...');
            }

            // Vérifier les rôles
            if (in_array('ROLE_ADMIN', $adminUser->getRoles())) {
                $io->success('✅ Rôle admin présent');
            } else {
                $io->error('❌ Rôle admin manquant');
            }

            // Vérifier le statut de vérification
            if ($adminUser->isVerified()) {
                $io->success('✅ Compte admin vérifié');
            } else {
                $io->error('❌ Compte admin non vérifié');
            }

            // 3. Nettoyer les utilisateurs non vérifiés (sauf admin)
            $io->section('🧹 Nettoyage recommandé');
            
            $unverifiedUsers = $this->entityManager->getRepository(User::class)->findBy(['isVerified' => false]);
            $nonAdminUnverified = array_filter($unverifiedUsers, function($user) {
                return !in_array('ROLE_ADMIN', $user->getRoles());
            });

            if (count($nonAdminUnverified) > 0) {
                $io->warning('Utilisateurs non vérifiés détectés (hors admin) : ' . count($nonAdminUnverified));
                
                foreach ($nonAdminUnverified as $user) {
                    $io->text('- ' . $user->getEmail() . ' (créé le ' . 
                        ($user->getCreatedAt() ? $user->getCreatedAt()->format('Y-m-d H:i') : 'N/A') . ')');
                }
                
                $io->note('Commande pour nettoyer : php bin/console app:cleanup-unverified-users');
            } else {
                $io->success('✅ Aucun utilisateur non vérifié à nettoyer');
            }

            // 4. Résumé et recommandations
            $io->section('📋 Résumé');
            
            if ($adminUser && $isPasswordValid && $adminUser->isVerified() && in_array('ROLE_ADMIN', $adminUser->getRoles())) {
                $io->success('🎉 Système d\'authentification fonctionnel !');
                $io->listing([
                    'Email admin : ' . $adminEmail,
                    'Mot de passe : King97232/',
                    'URL de connexion : http://127.0.0.1:8000/login'
                ]);
                
                $io->note('Si vous avez encore des problèmes, essayez :');
                $io->listing([
                    'Navigation privée/incognito',
                    'Vider le cache du navigateur',
                    'Redémarrer le serveur Symfony'
                ]);
            } else {
                $io->error('❌ Problèmes détectés dans le système d\'authentification');
                $io->note('Recréez l\'utilisateur admin avec : php bin/console app:recreate-admin');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Erreur lors du diagnostic : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
