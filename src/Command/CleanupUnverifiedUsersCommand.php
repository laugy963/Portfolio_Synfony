<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:cleanup-unverified-users',
    description: 'Supprime les comptes utilisateur non vérifiés après un délai d\'expiration'
)]
class CleanupUnverifiedUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'days',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Nombre de jours après lesquels supprimer les comptes non vérifiés',
                7
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Simuler la suppression sans effectuer d\'action réelle'
            )
            ->setHelp('
Cette commande supprime automatiquement les comptes utilisateur qui ne sont pas vérifiés
après un délai spécifié (par défaut 7 jours).

Exemples d\'utilisation :
  <info>php bin/console app:cleanup-unverified-users</info>
  <info>php bin/console app:cleanup-unverified-users --days=3</info>
  <info>php bin/console app:cleanup-unverified-users --dry-run</info>
            ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $days = (int) $input->getOption('days');
        $dryRun = $input->getOption('dry-run');

        $io->title('🧹 Nettoyage des comptes non vérifiés');

        // Calculer la date limite
        $cutoffDate = new \DateTimeImmutable("-{$days} days");
        
        if ($dryRun) {
            $io->note('Mode simulation activé - aucune suppression ne sera effectuée');
        }

        $io->info("Recherche des comptes non vérifiés créés avant le {$cutoffDate->format('d/m/Y H:i:s')}");

        // Trouver les utilisateurs non vérifiés
        $userRepository = $this->entityManager->getRepository(User::class);
        $unverifiedUsers = $userRepository->createQueryBuilder('u')
            ->where('(u.isVerified IS NULL OR u.isVerified = :false)')
            ->andWhere('u.createdAt < :cutoffDate')
            ->setParameter('false', false)
            ->setParameter('cutoffDate', $cutoffDate)
            ->getQuery()
            ->getResult();

        $count = count($unverifiedUsers);

        if ($count === 0) {
            $io->success('Aucun compte non vérifié à supprimer.');
            return Command::SUCCESS;
        }

        $io->warning("Trouvé {$count} compte(s) non vérifié(s) à supprimer :");

        // Afficher les détails des comptes à supprimer
        $tableData = [];
        foreach ($unverifiedUsers as $user) {
            $tableData[] = [
                $user->getId(),
                $user->getEmail(),
                $user->getFirstName() . ' ' . $user->getLastName(),
                $user->getCreatedAt()->format('d/m/Y H:i:s'),
                $user->isVerified() ? '✅' : '❌'
            ];
        }

        $io->table(
            ['ID', 'Email', 'Nom complet', 'Créé le', 'Vérifié'],
            $tableData
        );

        if (!$dryRun) {
            if (!$io->confirm('Êtes-vous sûr de vouloir supprimer ces comptes ?', false)) {
                $io->info('Opération annulée.');
                return Command::SUCCESS;
            }

            // Supprimer les utilisateurs
            foreach ($unverifiedUsers as $user) {
                $this->entityManager->remove($user);
                $io->writeln("🗑️  Suppression de : {$user->getEmail()}");
            }

            $this->entityManager->flush();
            $io->success("{$count} compte(s) non vérifié(s) supprimé(s) avec succès !");
        } else {
            $io->info("Mode simulation : {$count} compte(s) auraient été supprimé(s).");
        }

        // Statistiques finales
        $remainingUsers = $userRepository->count([]);
        $verifiedUsers = $userRepository->count(['isVerified' => true]);
        
        $io->section('📊 Statistiques de la base de données');
        $io->definitionList(
            ['Utilisateurs restants' => $remainingUsers],
            ['Utilisateurs vérifiés' => $verifiedUsers],
            ['Utilisateurs non vérifiés' => $remainingUsers - $verifiedUsers]
        );

        return Command::SUCCESS;
    }
}
