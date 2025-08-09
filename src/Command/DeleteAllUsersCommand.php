<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:delete-all-users',
    description: 'Supprime tous les utilisateurs de la base de données',
)]
class DeleteAllUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Suppression de tous les utilisateurs');

        try {
            // Compter le nombre d'utilisateurs
            $userCount = $this->entityManager->getRepository(User::class)->count([]);
            
            if ($userCount === 0) {
                $io->info('Aucun utilisateur à supprimer.');
                return Command::SUCCESS;
            }

            $io->warning('Cette action va supprimer ' . $userCount . ' utilisateur(s) de la base de données.');
            $io->warning('Cette action est IRRÉVERSIBLE !');

            // Demander confirmation
            if (!$io->confirm('Êtes-vous sûr de vouloir supprimer tous les utilisateurs ?', false)) {
                $io->info('Opération annulée.');
                return Command::SUCCESS;
            }

            // Lister les utilisateurs avant suppression
            $users = $this->entityManager->getRepository(User::class)->findAll();
            
            $io->section('Utilisateurs qui vont être supprimés :');
            $tableData = [];
            foreach ($users as $user) {
                $tableData[] = [
                    $user->getId(),
                    $user->getEmail(),
                    $user->getFirstName() . ' ' . $user->getLastName(),
                    implode(', ', $user->getRoles()),
                ];
            }
            
            $io->table(['ID', 'Email', 'Nom', 'Rôles'], $tableData);

            // Supprimer tous les utilisateurs
            $io->progressStart($userCount);
            
            foreach ($users as $user) {
                $this->entityManager->remove($user);
                $io->progressAdvance();
            }
            
            $this->entityManager->flush();
            $io->progressFinish();

            $io->success('✅ Tous les utilisateurs ont été supprimés avec succès !');
            $io->info('Total supprimé : ' . $userCount . ' utilisateur(s)');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Erreur lors de la suppression : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
