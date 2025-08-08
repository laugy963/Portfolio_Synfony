<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class ScheduledCleanupService
{
    public function __construct(
        private UserCleanupService $userCleanupService,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Exécute le nettoyage automatique quotidien
     */
    public function runDailyCleanup(): array
    {
        $this->logger->info('Démarrage du nettoyage automatique quotidien');

        $results = [
            'started_at' => new \DateTimeImmutable(),
            'expired_codes' => null,
            'unverified_users' => null,
            'errors' => []
        ];

        try {
            // 1. Nettoyer les codes de vérification expirés
            $results['expired_codes'] = $this->userCleanupService->cleanupExpiredVerificationCodes();
            $this->logger->info('Codes expirés nettoyés', $results['expired_codes']);

            // 2. Supprimer les comptes non vérifiés (après 7 jours par défaut)
            $results['unverified_users'] = $this->userCleanupService->cleanupUnverifiedUsers(7, false);
            $this->logger->info('Utilisateurs non vérifiés nettoyés', $results['unverified_users']);

        } catch (\Exception $e) {
            $error = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
            $results['errors'][] = $error;
            $this->logger->error('Erreur lors du nettoyage automatique', $error);
        }

        $results['completed_at'] = new \DateTimeImmutable();
        $this->logger->info('Nettoyage automatique terminé', [
            'duration' => $results['completed_at']->diff($results['started_at'])->format('%s secondes')
        ]);

        return $results;
    }

    /**
     * Exécute le nettoyage hebdomadaire plus approfondi
     */
    public function runWeeklyCleanup(): array
    {
        $this->logger->info('Démarrage du nettoyage automatique hebdomadaire');

        $results = [
            'started_at' => new \DateTimeImmutable(),
            'expired_codes' => null,
            'unverified_users' => null,
            'statistics' => null,
            'errors' => []
        ];

        try {
            // 1. Nettoyer les codes expirés
            $results['expired_codes'] = $this->userCleanupService->cleanupExpiredVerificationCodes();

            // 2. Supprimer les comptes non vérifiés après 3 jours (plus agressif)
            $results['unverified_users'] = $this->userCleanupService->cleanupUnverifiedUsers(3, false);

            // 3. Obtenir les statistiques finales
            $results['statistics'] = $this->userCleanupService->getUserStatistics();

            $this->logger->info('Nettoyage hebdomadaire terminé', $results);

        } catch (\Exception $e) {
            $error = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
            $results['errors'][] = $error;
            $this->logger->error('Erreur lors du nettoyage hebdomadaire', $error);
        }

        $results['completed_at'] = new \DateTimeImmutable();
        return $results;
    }

    /**
     * Génère un rapport de nettoyage
     */
    public function generateCleanupReport(array $results): string
    {
        $report = "🧹 RAPPORT DE NETTOYAGE AUTOMATIQUE\n";
        $report .= "===================================\n\n";
        
        $report .= "📅 Exécuté le : " . $results['started_at']->format('d/m/Y à H:i:s') . "\n";
        if (isset($results['completed_at'])) {
            $duration = $results['completed_at']->diff($results['started_at']);
            $report .= "⏱️  Durée : " . $duration->format('%i minutes %s secondes') . "\n\n";
        }

        // Codes expirés
        if ($results['expired_codes']) {
            $codes = $results['expired_codes'];
            $report .= "🔑 CODES DE VÉRIFICATION EXPIRÉS\n";
            $report .= "Trouvés : {$codes['found_count']}\n";
            $report .= "Nettoyés : {$codes['cleaned_count']}\n\n";
        }

        // Utilisateurs non vérifiés
        if ($results['unverified_users']) {
            $users = $results['unverified_users'];
            $report .= "👤 UTILISATEURS NON VÉRIFIÉS\n";
            $report .= "Trouvés : {$users['found_count']}\n";
            $report .= "Supprimés : {$users['deleted_count']}\n\n";
            
            if (!empty($users['deleted_users'])) {
                $report .= "Comptes supprimés :\n";
                foreach ($users['deleted_users'] as $user) {
                    $report .= "- {$user['email']} (créé le {$user['created_at']})\n";
                }
                $report .= "\n";
            }
        }

        // Statistiques finales
        if (isset($results['statistics'])) {
            $stats = $results['statistics'];
            $report .= "📊 STATISTIQUES FINALES\n";
            $report .= "Total utilisateurs : {$stats['total_users']}\n";
            $report .= "Utilisateurs vérifiés : {$stats['verified_users']}\n";
            $report .= "Utilisateurs non vérifiés : {$stats['unverified_users']}\n";
            $report .= "Taux de vérification : {$stats['verification_rate']}%\n\n";
        }

        // Erreurs
        if (!empty($results['errors'])) {
            $report .= "❌ ERREURS RENCONTRÉES\n";
            foreach ($results['errors'] as $error) {
                $report .= "- {$error['message']}\n";
            }
            $report .= "\n";
        }

        $report .= "✅ Nettoyage terminé avec succès !\n";
        
        return $report;
    }
}
