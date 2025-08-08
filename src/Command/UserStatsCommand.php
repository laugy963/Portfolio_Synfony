<?php

namespace App\Command;

use App\Service\UserCleanupService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user-stats',
    description: 'Affiche les statistiques des utilisateurs'
)]
class UserStatsCommand extends Command
{
    public function __construct(
        private UserCleanupService $cleanupService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('📊 Statistiques des utilisateurs');

        $stats = $this->cleanupService->getUserStatistics();

        $io->definitionList(
            ['👥 Total utilisateurs' => $stats['total_users']],
            ['✅ Utilisateurs vérifiés' => $stats['verified_users']],
            ['❌ Utilisateurs non vérifiés' => $stats['unverified_users']],
            ['⏰ Codes expirés' => $stats['users_with_expired_codes']],
            ['📈 Taux de vérification' => $stats['verification_rate'] . '%']
        );

        // Affichage coloré basé sur le taux de vérification
        if ($stats['verification_rate'] >= 80) {
            $io->success('Excellent taux de vérification !');
        } elseif ($stats['verification_rate'] >= 60) {
            $io->note('Bon taux de vérification.');
        } else {
            $io->warning('Taux de vérification faible - considérez le nettoyage des comptes non vérifiés.');
        }

        // Suggestions d'actions
        if ($stats['unverified_users'] > 0) {
            $io->section('💡 Actions recommandées');
            $io->listing([
                'Nettoyer les codes expirés : <info>php bin/console app:cleanup-expired-codes</info>',
                'Supprimer les comptes non vérifiés : <info>php bin/console app:cleanup-unverified-users</info>',
                'Simulation de nettoyage : <info>php bin/console app:cleanup-unverified-users --dry-run</info>'
            ]);
        }

        return Command::SUCCESS;
    }
}
