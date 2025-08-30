<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;
    private string $adminEmail;
    private string $adminFirstName;
    private string $adminLastName;
    private string $adminPassword;

    public function __construct(
        UserPasswordHasherInterface $hasher,
        string $adminEmail,
        string $adminFirstName,
        string $adminLastName,
        string $adminPassword
    ) {
        $this->hasher = $hasher;
        $this->adminEmail = $adminEmail;
        $this->adminFirstName = $adminFirstName;
        $this->adminLastName = $adminLastName;
        $this->adminPassword = $adminPassword;
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail($this->adminEmail);
        $admin->setFirstName($this->adminFirstName);
        $admin->setLastName($this->adminLastName);
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);

        $password = $this->hasher->hashPassword($admin, $this->adminPassword);
        $admin->setPassword($password);

        $manager->persist($admin);
        $manager->flush();
    }
}
