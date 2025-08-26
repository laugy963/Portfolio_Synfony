<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

$dsn = 'smtp://laukingportfolio@gmail.com:auwfdjpbhgpxdknp@smtp.gmail.com:587?encryption=tls&auth_mode=login';
$transport = Transport::fromDsn($dsn);
$mailer = new Mailer($transport);

$email = (new Email())
    ->from('laukingportfolio@gmail.com')
    ->to('laukingstephane@gmail.com')
    ->subject('Test Symfony Mailer')
    ->text('Ceci est un test d\'envoi depuis Symfony Mailer.');

try {
    $mailer->send($email);
    echo "✅ Email envoyé avec succès.\n";
} catch (\Throwable $e) {
    echo "❌ Erreur lors de l'envoi : " . $e->getMessage() . "\n";
}
