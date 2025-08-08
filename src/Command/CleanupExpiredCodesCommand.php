<?php

namespace App\Command;

use App\Service\UserCleanupService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cleanup-expired-codes',
    description: 'Nettoie les codes de vérification expirés'
)]
class CleanupExpiredCodesCommand extends Command
{
    public function __construct(
        private UserCleanupService $cleanupService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('🧹 Nettoyage des codes de vérification expirés');

        // Nettoyer les codes expirés
        $result = $this->cleanupService->cleanupExpiredVerificationCodes();

        if ($result['found_count'] === 0) {
            $io->success('Aucun code de vérification expiré à nettoyer.');
            return Command::SUCCESS;
        }

        $io->warning("Trouvé {$result['found_count']} code(s) de vérification expiré(s)");

        // Afficher les détails
        if (!empty($result['cleaned_users'])) {
            $tableData = [];
            foreach ($result['cleaned_users'] as $userInfo) {
                $tableData[] = [
                    $userInfo['id'],
                    $userInfo['email'],
                    $userInfo['expired_at']
                ];
            }

            $io->table(
                ['ID', 'Email', 'Expiré le'],
                $tableData
            );
        }

        $io->success("{$result['cleaned_count']} code(s) de vérification expiré(s) nettoyé(s) !");

        // Afficher les statistiques
        $stats = $this->cleanupService->getUserStatistics();
        $io->section('📊 Statistiques des utilisateurs');
        $io->definitionList(
            ['Total utilisateurs' => $stats['total_users']],
            ['Utilisateurs vérifiés' => $stats['verified_users']],
            ['Utilisateurs non vérifiés' => $stats['unverified_users']],
            ['Taux de vérification' => $stats['verification_rate'] . '%']
        );

        return Command::SUCCESS;
    }
}
