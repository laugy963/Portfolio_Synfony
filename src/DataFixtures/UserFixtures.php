<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Récupération des informations depuis les variables d'environnement
        $adminEmail = $_ENV['ADMIN_EMAIL'] ?? 'admin@portfolio.com';
        $adminFirstName = $_ENV['ADMIN_FIRSTNAME'] ?? 'Admin';
        $adminLastName = $_ENV['ADMIN_LASTNAME'] ?? 'Portfolio';
        $adminPassword = $_ENV['ADMIN_PASSWORD'] ?? 'admin123';
        
        // Création de l'utilisateur administrateur
        $admin = new User();
        $admin->setEmail($adminEmail);
        $admin->setFirstName($adminFirstName);
        $admin->setLastName($adminLastName);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setCreatedAt(new \DateTimeImmutable());
        
        // Hash du mot de passe depuis la variable d'environnement
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            $adminPassword
        );
        $admin->setPassword($hashedPassword);

        $manager->persist($admin);
        $manager->flush();
    }
}
