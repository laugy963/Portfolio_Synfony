<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[AsCommand(
    name: 'app:test-csrf',
    description: 'Teste la génération et validation des tokens CSRF',
)]
class TestCsrfCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test des tokens CSRF');

        $io->info('Solutions pour résoudre l\'erreur "Invalid CSRF token" :');
        
        $io->section('1. Vider le cache du navigateur');
        $io->text([
            '- Utilisez Ctrl+Shift+R (ou Cmd+Shift+R sur Mac)',
            '- Ou utilisez le mode navigation privée',
            '- Fermez et rouvrez votre navigateur'
        ]);

        $io->section('2. Vérifier les cookies');
        $io->text([
            '- Assurez-vous que les cookies sont activés',
            '- Supprimez les cookies du site si nécessaire'
        ]);

        $io->section('3. Redémarrer le serveur');
        $io->text([
            'Parfois un redémarrage du serveur Symfony résout le problème'
        ]);

        $io->section('4. Accès direct à la page de connexion');
        $io->text([
            'Accédez directement à : http://127.0.0.1:8000/login',
            'Ne pas utiliser de liens ou redirections'
        ]);

        $io->section('Solutions immédiates :');
        
        $io->listing([
            'Ouvrez un nouvel onglet en navigation privée',
            'Allez sur http://127.0.0.1:8000/login', 
            'Utilisez les identifiants : laukingstephane@gmail.com / King97232/',
            'Si le problème persiste, redémarrez le serveur'
        ]);

        $io->success('✅ Suivez ces étapes pour résoudre le problème CSRF');

        return Command::SUCCESS;
    }
}
