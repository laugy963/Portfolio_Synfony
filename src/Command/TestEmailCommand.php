<?php

namespace App\Command;

use App\Service\AppEmailFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:test-email',
    description: 'Test d\'envoi d\'email pour vérifier la configuration',
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly AppEmailFactory $emailFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Adresse email de destination')
            ->setHelp('Cette commande permet de tester l\'envoi d\'email avec votre configuration Gmail.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $emailAddress = $input->getArgument('email');

        $io->title('🧪 Test d\'envoi d\'email');

        try {
            $email = (new Email())
                ->from($this->emailFactory->getFromAddress())
                ->to($emailAddress)
                ->subject('Test de configuration - Portfolio')
                ->html('<h1>Test réussi ! ✅</h1><p>Votre configuration Gmail fonctionne parfaitement.</p>')
                ->text('Test réussi ! Votre configuration Gmail fonctionne parfaitement.');

            $this->mailer->send($email);

            $io->success([
                'Email envoyé avec succès !',
                'Destination: ' . $emailAddress,
                'Expéditeur: ' . $this->emailFactory->getFromAddress()->toString()
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error([
                'Erreur lors de l\'envoi de l\'email:',
                $e->getMessage()
            ]);

            $io->note([
                'Vérifiez que:',
                '1. Le mot de passe d\'application Gmail est correct',
                '2. La vérification en 2 étapes est activée sur Gmail',
                '3. Votre connexion internet fonctionne'
            ]);

            return Command::FAILURE;
        }
    }
}
