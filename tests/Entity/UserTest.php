<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testUserCreation(): void
    {
        $this->assertInstanceOf(User::class, $this->user);
        $this->assertNull($this->user->getId());
        $this->assertFalse($this->user->isVerified());
        $this->assertEquals(['ROLE_USER'], $this->user->getRoles());
    }

    public function testEmailSetterAndGetter(): void
    {
        $email = 'test@example.com';
        $this->user->setEmail($email);
        
        $this->assertEquals($email, $this->user->getEmail());
        $this->assertEquals($email, $this->user->getUserIdentifier());
    }

    public function testNameSettersAndGetters(): void
    {
        $firstName = 'John';
        $lastName = 'Doe';
        
        $this->user->setFirstName($firstName);
        $this->user->setLastName($lastName);
        
        $this->assertEquals($firstName, $this->user->getFirstName());
        $this->assertEquals($lastName, $this->user->getLastName());
        $this->assertEquals($firstName . ' ' . $lastName, $this->user->getFullName());
    }

    public function testFullNameWithPartialNames(): void
    {
        // Test avec seulement le prénom
        $this->user->setFirstName('John');
        $this->assertEquals('John', $this->user->getFullName());
        
        // Test avec seulement le nom
        $this->user->setFirstName(null);
        $this->user->setLastName('Doe');
        $this->assertEquals('Doe', $this->user->getFullName());
        
        // Test sans nom ni prénom
        $this->user->setLastName(null);
        $this->assertEquals('', $this->user->getFullName());
    }

    public function testVerificationCodeSettersAndGetters(): void
    {
        $code = '123456';
        $expiresAt = new \DateTimeImmutable('+15 minutes');
        
        $this->user->setVerificationCode($code);
        $this->user->setVerificationCodeExpiresAt($expiresAt);
        
        $this->assertEquals($code, $this->user->getVerificationCode());
        $this->assertEquals($expiresAt, $this->user->getVerificationCodeExpiresAt());
    }

    public function testIsVerifiedSetterAndGetter(): void
    {
        $this->assertFalse($this->user->isVerified());
        
        $this->user->setIsVerified(true);
        $this->assertTrue($this->user->isVerified());
    }

    public function testCreatedAtSetterAndGetter(): void
    {
        $createdAt = new \DateTimeImmutable();
        $this->user->setCreatedAt($createdAt);
        
        $this->assertEquals($createdAt, $this->user->getCreatedAt());
    }

    public function testLastLoginAtSetterAndGetter(): void
    {
        $lastLoginAt = new \DateTime();
        $this->user->setLastLoginAt($lastLoginAt);
        
        $this->assertEquals($lastLoginAt, $this->user->getLastLoginAt());
    }

    public function testRolesSetterAndGetter(): void
    {
        $roles = ['ROLE_USER', 'ROLE_ADMIN'];
        $this->user->setRoles($roles);
        
        $this->assertEquals($roles, $this->user->getRoles());
    }

    public function testRolesAlwaysContainRoleUser(): void
    {
        // Définir des rôles sans ROLE_USER
        $this->user->setRoles(['ROLE_ADMIN']);
        
        // ROLE_USER devrait toujours être présent
        $this->assertContains('ROLE_USER', $this->user->getRoles());
    }

    public function testPasswordSetterAndGetter(): void
    {
        $password = 'hashed_password_here';
        $this->user->setPassword($password);
        
        $this->assertEquals($password, $this->user->getPassword());
    }

    public function testEraseCredentials(): void
    {
        // Cette méthode devrait être vide (pour des raisons de sécurité)
        $this->user->eraseCredentials();
        
        // Pas d'assertion spécifique car la méthode est vide
        // Mais on s'assure qu'elle ne lève pas d'exception
        $this->assertTrue(true);
    }

    public function testSerialization(): void
    {
        $this->user->setEmail('test@example.com');
        $this->user->setPassword('password');
        
        $serialized = $this->user->__serialize();
        
        $this->assertIsArray($serialized);
        // Les clés dans __serialize() utilisent le format "\0ClassName\0property"
        $this->assertArrayHasKey("\0App\\Entity\\User\0email", $serialized);
        $this->assertEquals('test@example.com', $serialized["\0App\\Entity\\User\0email"]);
    }

    public function testVerificationCodeExpiration(): void
    {
        // Test avec un code non expiré
        $futureTime = new \DateTimeImmutable('+10 minutes');
        $this->user->setVerificationCodeExpiresAt($futureTime);
        $this->assertFalse($this->user->isVerificationCodeExpired());

        // Test avec un code expiré
        $pastTime = new \DateTimeImmutable('-10 minutes');
        $this->user->setVerificationCodeExpiresAt($pastTime);
        $this->assertTrue($this->user->isVerificationCodeExpired());

        // Test sans date d'expiration
        $this->user->setVerificationCodeExpiresAt(null);
        $this->assertFalse($this->user->isVerificationCodeExpired());
    }
}
