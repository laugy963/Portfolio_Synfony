<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Récupération des infos depuis les variables d'environnement
        $email = getenv('ADMIN_EMAIL') ?: 'admin@example.com';
        $firstName = getenv('ADMIN_FIRSTNAME') ?: 'Admin';
        $lastName = getenv('ADMIN_LASTNAME') ?: 'User';
        $plainPassword = getenv('ADMIN_PASSWORD') ?: 'motdepasseadmin';

        $admin = new User();
        $admin->setEmail($email);
        $admin->setFirstName($firstName);
        $admin->setLastName($lastName);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);

        $password = $this->hasher->hashPassword($admin, $plainPassword);
        $admin->setPassword($password);

        $manager->persist($admin);
        $manager->flush();
    }
}
