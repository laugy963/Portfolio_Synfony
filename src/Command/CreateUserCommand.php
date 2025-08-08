<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Créer un nouvel utilisateur administrateur',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'utilisateur')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe de l\'utilisateur')
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Donner les droits administrateur')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $isAdmin = $input->getOption('admin');

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        
        if ($existingUser) {
            $io->error('Un utilisateur avec cet email existe déjà !');
            return Command::FAILURE;
        }

        // Créer le nouvel utilisateur
        $user = new User();
        $user->setEmail($email);
        
        // Définir les rôles
        $roles = ['ROLE_USER'];
        if ($isAdmin) {
            $roles[] = 'ROLE_ADMIN';
        }
        $user->setRoles($roles);
        
        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        
        // Définir la date de création
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setIsVerified(true); // Utilisateur vérifié par défaut

        // Sauvegarder en base
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $roleText = $isAdmin ? 'administrateur' : 'utilisateur';
        $io->success("Utilisateur $roleText créé avec succès !");
        $io->table(
            ['Propriété', 'Valeur'],
            [
                ['Email', $email],
                ['Rôles', implode(', ', $roles)],
                ['Créé le', (new \DateTime())->format('Y-m-d H:i:s')]
            ]
        );

        return Command::SUCCESS;
    }
}
