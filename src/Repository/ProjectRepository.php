<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * Récupère les projets les plus récents
     * @return Project[] Returns an array of recent Project objects
     */
    public function findRecentProjects(int $limit = 6): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Recherche des projets par technologie
     * @return Project[] Returns an array of Project objects
     */
    public function findByTechnology(string $technology): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.technologies LIKE :technology')
            ->setParameter('technology', '%' . $technology . '%')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Récupère tous les projets triés par position puis par date de création
     * @return Project[] Returns an array of Project objects ordered by position
     */
    public function findAllOrderedByPosition(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.position', 'ASC')
            ->addOrderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Récupère la prochaine position disponible
     * @return int Returns the next available position
     */
    public function getNextPosition(): int
    {
        $result = $this->createQueryBuilder('p')
            ->select('MAX(p.position) as maxPosition')
            ->getQuery()
            ->getSingleScalarResult();
        
        return ($result ? (int)$result : 0) + 1;
    }

    /**
     * @return Project[]
     */
    public function findHomepageProjects(int $limit = 6): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.position', 'ASC')
            ->addOrderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function mediaFilenameExists(string $filename, ?int $excludeProjectId = null): bool
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.bannerImage = :filename OR p.images LIKE :imageNeedle')
            ->setParameter('filename', $filename)
            ->setParameter('imageNeedle', '%"' . $filename . '"%');

        if ($excludeProjectId !== null) {
            $queryBuilder
                ->andWhere('p.id != :excludeProjectId')
                ->setParameter('excludeProjectId', $excludeProjectId);
        }

        return (int) $queryBuilder->getQuery()->getSingleScalarResult() > 0;
    }
}
