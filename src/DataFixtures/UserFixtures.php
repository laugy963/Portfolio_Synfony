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
        // Création de l'utilisateur administrateur
        $admin = new User();
        $admin->setEmail('admin@portfolio.com');
        $admin->setRoles(['ROLE_ADMIN']);
        
        // Hash du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword(
            $admin,
            'admin123' // Mot de passe par défaut
        );
        $admin->setPassword($hashedPassword);

        $manager->persist($admin);
        $manager->flush();
    }
}
