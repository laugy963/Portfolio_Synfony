<?php

namespace App\Command;

use App\Service\ScheduledCleanupService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:scheduled-cleanup',
    description: 'Exécute le nettoyage automatique planifié (quotidien ou hebdomadaire)'
)]
class ScheduledCleanupCommand extends Command
{
    public function __construct(
        private ScheduledCleanupService $scheduledCleanupService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'type',
                't',
                InputOption::VALUE_OPTIONAL,
                'Type de nettoyage (daily|weekly)',
                'daily'
            )
            ->addOption(
                'report',
                'r',
                InputOption::VALUE_NONE,
                'Afficher un rapport détaillé'
            )
            ->setHelp('
Cette commande exécute le nettoyage automatique planifié.

Types de nettoyage disponibles :
- <info>daily</info> : Nettoyage quotidien (codes expirés + comptes non vérifiés après 7 jours)
- <info>weekly</info> : Nettoyage hebdomadaire (plus agressif, comptes après 3 jours)

Exemples d\'utilisation :
  <info>php bin/console app:scheduled-cleanup</info>
  <info>php bin/console app:scheduled-cleanup --type=weekly</info>
  <info>php bin/console app:scheduled-cleanup --report</info>
            ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $type = $input->getOption('type');
        $showReport = $input->getOption('report');

        if (!in_array($type, ['daily', 'weekly'])) {
            $io->error('Type de nettoyage invalide. Utilisez "daily" ou "weekly".');
            return Command::FAILURE;
        }

        $io->title("🤖 Nettoyage automatique - " . ($type === 'daily' ? 'Quotidien' : 'Hebdomadaire'));

        try {
            $results = match ($type) {
                'daily' => $this->scheduledCleanupService->runDailyCleanup(),
                'weekly' => $this->scheduledCleanupService->runWeeklyCleanup(),
            };

            // Affichage des résultats
            $this->displayResults($io, $results, $type);

            // Rapport détaillé si demandé
            if ($showReport) {
                $report = $this->scheduledCleanupService->generateCleanupReport($results);
                $io->section('📄 Rapport détaillé');
                $io->text($report);
            }

            $io->success('Nettoyage automatique terminé avec succès !');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Erreur lors du nettoyage automatique : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function displayResults(SymfonyStyle $io, array $results, string $type): void
    {
        $io->section('📊 Résultats du nettoyage');

        // Codes expirés
        if (isset($results['expired_codes'])) {
            $codes = $results['expired_codes'];
            $io->definitionList(
                ['🔑 Codes expirés trouvés' => $codes['found_count']],
                ['🧹 Codes nettoyés' => $codes['cleaned_count']]
            );
        }

        // Utilisateurs non vérifiés
        if (isset($results['unverified_users'])) {
            $users = $results['unverified_users'];
            $io->definitionList(
                ['👤 Comptes non vérifiés trouvés' => $users['found_count']],
                ['🗑️  Comptes supprimés' => $users['deleted_count']]
            );

            if ($users['deleted_count'] > 0) {
                $io->warning("Suppression de {$users['deleted_count']} compte(s) non vérifié(s)");
            }
        }

        // Nettoyage spécifique hebdomadaire
        if ($type === 'weekly' && isset($results['unverified_users'])) {
            $users = $results['unverified_users'];
            $io->note("Nettoyage agressif (3 jours) : {$users['deleted_count']} compte(s) supprimé(s)");
        }

        // Statistiques finales
        if (isset($results['statistics'])) {
            $stats = $results['statistics'];
            $io->section('📈 Statistiques finales');
            $io->definitionList(
                ['Total utilisateurs' => $stats['total_users']],
                ['Utilisateurs vérifiés' => $stats['verified_users']],
                ['Taux de vérification' => $stats['verification_rate'] . '%']
            );
        }

        // Erreurs
        if (!empty($results['errors'])) {
            $io->section('❌ Erreurs rencontrées');
            foreach ($results['errors'] as $error) {
                $io->error($error['message']);
            }
        }

        // Durée d'exécution
        if (isset($results['started_at'], $results['completed_at'])) {
            $duration = $results['completed_at']->diff($results['started_at']);
            $io->note('Durée d\'exécution : ' . $duration->format('%i min %s sec'));
        }
    }
}
