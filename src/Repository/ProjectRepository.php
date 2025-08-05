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
     * Récupère les projets en vedette pour la page d'accueil
     * @return Project[] Returns an array of featured Project objects
     */
    public function findFeaturedProjects(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.featured = :featured')
            ->setParameter('featured', true)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
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
}
