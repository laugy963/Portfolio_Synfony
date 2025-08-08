<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class UserCleanupService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Supprime les utilisateurs non vérifiés après un délai spécifié
     */
    public function cleanupUnverifiedUsers(int $daysOld = 7, bool $dryRun = false): array
    {
        $cutoffDate = new \DateTimeImmutable("-{$daysOld} days");
        
        $this->logger->info('Démarrage du nettoyage des utilisateurs non vérifiés', [
            'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
            'days_old' => $daysOld,
            'dry_run' => $dryRun
        ]);

        // Trouver les utilisateurs non vérifiés
        $userRepository = $this->entityManager->getRepository(User::class);
        $unverifiedUsers = $userRepository->createQueryBuilder('u')
            ->where('(u.isVerified IS NULL OR u.isVerified = :false)')
            ->andWhere('u.createdAt < :cutoffDate')
            ->setParameter('false', false)
            ->setParameter('cutoffDate', $cutoffDate)
            ->getQuery()
            ->getResult();

        $result = [
            'found_count' => count($unverifiedUsers),
            'deleted_count' => 0,
            'deleted_users' => [],
            'dry_run' => $dryRun
        ];

        if (empty($unverifiedUsers)) {
            $this->logger->info('Aucun utilisateur non vérifié à supprimer');
            return $result;
        }

        // Collecter les informations des utilisateurs à supprimer
        foreach ($unverifiedUsers as $user) {
            $userInfo = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getFirstName() . ' ' . $user->getLastName(),
                'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s')
            ];
            
            $result['deleted_users'][] = $userInfo;

            if (!$dryRun) {
                $this->entityManager->remove($user);
                $result['deleted_count']++;
                
                $this->logger->info('Utilisateur non vérifié supprimé', $userInfo);
            }
        }

        if (!$dryRun && $result['deleted_count'] > 0) {
            $this->entityManager->flush();
            $this->logger->info('Nettoyage terminé', [
                'deleted_count' => $result['deleted_count']
            ]);
        }

        return $result;
    }

    /**
     * Supprime les codes de vérification expirés
     */
    public function cleanupExpiredVerificationCodes(): array
    {
        $now = new \DateTimeImmutable();
        
        $this->logger->info('Démarrage du nettoyage des codes de vérification expirés');

        $userRepository = $this->entityManager->getRepository(User::class);
        $usersWithExpiredCodes = $userRepository->createQueryBuilder('u')
            ->where('u.verificationCodeExpiresAt IS NOT NULL')
            ->andWhere('u.verificationCodeExpiresAt < :now')
            ->andWhere('u.isVerified = :false OR u.isVerified IS NULL')
            ->setParameter('now', $now)
            ->setParameter('false', false)
            ->getQuery()
            ->getResult();

        $result = [
            'found_count' => count($usersWithExpiredCodes),
            'cleaned_count' => 0,
            'cleaned_users' => []
        ];

        foreach ($usersWithExpiredCodes as $user) {
            $userInfo = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'expired_at' => $user->getVerificationCodeExpiresAt()->format('Y-m-d H:i:s')
            ];

            // Effacer le code de vérification expiré
            $user->setVerificationCode(null);
            $user->setVerificationCodeExpiresAt(null);
            
            $result['cleaned_users'][] = $userInfo;
            $result['cleaned_count']++;
            
            $this->logger->info('Code de vérification expiré nettoyé', $userInfo);
        }

        if ($result['cleaned_count'] > 0) {
            $this->entityManager->flush();
            $this->logger->info('Nettoyage des codes expirés terminé', [
                'cleaned_count' => $result['cleaned_count']
            ]);
        }

        return $result;
    }

    /**
     * Obtient des statistiques sur les utilisateurs
     */
    public function getUserStatistics(): array
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        
        $totalUsers = $userRepository->count([]);
        $verifiedUsers = $userRepository->count(['isVerified' => true]);
        $unverifiedUsers = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.isVerified IS NULL OR u.isVerified = :false')
            ->setParameter('false', false)
            ->getQuery()
            ->getSingleScalarResult();

        $usersWithExpiredCodes = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.verificationCodeExpiresAt IS NOT NULL')
            ->andWhere('u.verificationCodeExpiresAt < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total_users' => $totalUsers,
            'verified_users' => $verifiedUsers,
            'unverified_users' => $unverifiedUsers,
            'users_with_expired_codes' => $usersWithExpiredCodes,
            'verification_rate' => $totalUsers > 0 ? round(($verifiedUsers / $totalUsers) * 100, 2) : 0
        ];
    }
}
