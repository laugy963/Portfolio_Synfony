<?php

use App\Kernel;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

/** @var ManagerRegistry $doctrine */
$doctrine = $kernel->getContainer()->get('doctrine');
$entityManager = $doctrine->getManager();
$metadata = $entityManager->getMetadataFactory()->getAllMetadata();

if ($metadata !== []) {
    $schemaTool = new SchemaTool($entityManager);
    $schemaTool->dropSchema($metadata);
    $schemaTool->createSchema($metadata);
}

$entityManager->close();
$kernel->shutdown();
