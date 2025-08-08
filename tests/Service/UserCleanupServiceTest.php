<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\UserCleanupService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class UserCleanupServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private LoggerInterface&MockObject $logger;
    private EntityRepository&MockObject $userRepository;
    private UserCleanupService $userCleanupService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->userRepository = $this->createMock(EntityRepository::class);
        
        $this->entityManager
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($this->userRepository);

        $this->userCleanupService = new UserCleanupService(
            $this->entityManager,
            $this->logger
        );
    }

    public function testCleanupUnverifiedUsersInDryRunMode(): void
    {
        // Arrange
        $users = [
            $this->createUnverifiedUser('test1@example.com'),
            $this->createUnverifiedUser('test2@example.com')
        ];

        $this->setupQueryBuilderMock($users);

        // Act
        $result = $this->userCleanupService->cleanupUnverifiedUsers(7, true);

        // Assert
        $this->assertEquals(2, $result['found_count']);
        $this->assertEquals(0, $result['deleted_count']);
        $this->assertTrue($result['dry_run']);
        $this->assertCount(2, $result['deleted_users']);
        
        // Vérifier qu'aucune suppression n'a été effectuée
        $this->entityManager
            ->expects($this->never())
            ->method('remove');
        
        $this->entityManager
            ->expects($this->never())
            ->method('flush');
    }

    public function testCleanupUnverifiedUsersWithRealDeletion(): void
    {
        // Arrange
        $users = [
            $this->createUnverifiedUser('test1@example.com'),
            $this->createUnverifiedUser('test2@example.com')
        ];

        $this->setupQueryBuilderMock($users);

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('remove');
        
        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Act
        $result = $this->userCleanupService->cleanupUnverifiedUsers(7, false);

        // Assert
        $this->assertEquals(2, $result['found_count']);
        $this->assertEquals(2, $result['deleted_count']);
        $this->assertFalse($result['dry_run']);
        $this->assertCount(2, $result['deleted_users']);
    }

    public function testCleanupUnverifiedUsersWithNoUsers(): void
    {
        // Arrange
        $this->setupQueryBuilderMock([]);

        // Act
        $result = $this->userCleanupService->cleanupUnverifiedUsers(7, false);

        // Assert
        $this->assertEquals(0, $result['found_count']);
        $this->assertEquals(0, $result['deleted_count']);
        $this->assertEmpty($result['deleted_users']);
        
        // Vérifier qu'aucune suppression n'a été tentée
        $this->entityManager
            ->expects($this->never())
            ->method('remove');
    }

    public function testCleanupExpiredVerificationCodes(): void
    {
        // Arrange
        $user1 = $this->createUserWithExpiredCode('test1@example.com');
        $user2 = $this->createUserWithExpiredCode('test2@example.com');
        $users = [$user1, $user2];

        $this->setupQueryBuilderMock($users);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // Act
        $result = $this->userCleanupService->cleanupExpiredVerificationCodes();

        // Assert
        $this->assertEquals(2, $result['found_count']);
        $this->assertEquals(2, $result['cleaned_count']);
        $this->assertCount(2, $result['cleaned_users']);

        // Vérifier que les codes ont été effacés
        $this->assertNull($user1->getVerificationCode());
        $this->assertNull($user1->getVerificationCodeExpiresAt());
        $this->assertNull($user2->getVerificationCode());
        $this->assertNull($user2->getVerificationCodeExpiresAt());
    }

    public function testGetUserStatistics(): void
    {
        // Arrange
        $this->userRepository
            ->method('count')
            ->willReturnMap([
                [[], 10], // total users
                [['isVerified' => true], 8] // verified users
            ]);

        // Mock pour les utilisateurs non vérifiés et codes expirés
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);
        
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);
        
        // Premier appel pour unverified_users, deuxième pour users_with_expired_codes
        $query->method('getSingleScalarResult')->willReturnOnConsecutiveCalls(2, 1);
        
        $this->userRepository
            ->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        // Act
        $stats = $this->userCleanupService->getUserStatistics();

        // Assert
        $this->assertEquals(10, $stats['total_users']);
        $this->assertEquals(8, $stats['verified_users']);
        $this->assertEquals(2, $stats['unverified_users']);
        $this->assertEquals(1, $stats['users_with_expired_codes']);
        $this->assertEquals(80.0, $stats['verification_rate']);
    }

    private function createUnverifiedUser(string $email): User
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(rand(1, 1000));
        $user->method('getEmail')->willReturn($email);
        $user->method('getFirstName')->willReturn('Test');
        $user->method('getLastName')->willReturn('User');
        $user->method('getCreatedAt')->willReturn(new \DateTimeImmutable('-10 days'));
        $user->method('isVerified')->willReturn(false);
        
        return $user;
    }

    private function createUserWithExpiredCode(string $email): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setVerificationCode('123456');
        $user->setVerificationCodeExpiresAt(new \DateTimeImmutable('-1 hour'));
        
        return $user;
    }

    private function setupQueryBuilderMock(array $users): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);
        
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);
        
        $query->method('getResult')->willReturn($users);
        
        $this->userRepository
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);
    }
}
