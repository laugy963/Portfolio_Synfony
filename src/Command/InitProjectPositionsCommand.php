<?php

namespace App\Command;

use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:init-project-positions',
    description: 'Initialise les positions des projets existants',
)]
class InitProjectPositionsCommand extends Command
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $projects = $this->projectRepository->findBy(['position' => null], ['createdAt' => 'ASC']);
        
        if (empty($projects)) {
            $io->success('Tous les projets ont déjà une position assignée.');
            return Command::SUCCESS;
        }

        $io->info(sprintf('Initialisation des positions pour %d projets...', count($projects)));

        foreach ($projects as $index => $project) {
            $project->setPosition($index + 1);
            $io->writeln(sprintf('Position %d assignée au projet: %s', $index + 1, $project->getTitle()));
        }

        $this->entityManager->flush();

        $io->success(sprintf('%d projets ont été mis à jour avec des positions.', count($projects)));

        return Command::SUCCESS;
    }
}
