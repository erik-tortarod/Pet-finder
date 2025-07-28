<?php

namespace App\Repository;

use App\Entity\FoundAnimals;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FoundAnimals>
 */
class FoundAnimalsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FoundAnimals::class);
    }

    /**
     * Find all found animals with all related data (animals, photos, users)
     */
    public function findAllWithRelationsPaginated(int $page = 1, int $limit = 10, array $filters = []): array
    {
        // Build the base query with filters
        $qb = $this->createQueryBuilder('fa')
            ->leftJoin('fa.animalId', 'a')
            ->leftJoin('a.animalPhotos', 'ap')
            ->leftJoin('a.animalTags', 'at')
            ->leftJoin('at.tagId', 't')
            ->leftJoin('fa.userId', 'u')
            ->where('a.status = :status')
            ->setParameter('status', 'FOUND');

        // Apply search filter
        if (!empty($filters['search'])) {
            $qb->andWhere('(a.name LIKE :search OR a.description LIKE :search OR fa.foundCircumstances LIKE :search)')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }

        // Apply animal type filter
        if (!empty($filters['animalType'])) {
            $qb->andWhere('a.animalType = :animalType')
                ->setParameter('animalType', $filters['animalType']);
        }

        // Apply zone filter
        if (!empty($filters['zone'])) {
            $qb->andWhere('fa.foundZone LIKE :zone')
                ->setParameter('zone', '%' . $filters['zone'] . '%');
        }

        // Apply tags filter
        if (!empty($filters['tags'])) {
            $qb->andWhere('t.name IN (:tags)')
                ->setParameter('tags', $filters['tags']);
        }

        // First, get the found animals IDs with pagination
        $foundAnimalIds = (clone $qb)
            ->select('fa.id')
            ->groupBy('fa.id')
            ->orderBy('fa.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        if (empty($foundAnimalIds)) {
            return [];
        }

        $ids = array_column($foundAnimalIds, 'id');

        // Then, get the complete data for those IDs
        return $this->createQueryBuilder('fa')
            ->leftJoin('fa.animalId', 'a')
            ->leftJoin('a.animalPhotos', 'ap')
            ->leftJoin('a.animalTags', 'at')
            ->leftJoin('at.tagId', 't')
            ->leftJoin('fa.userId', 'u')
            ->addSelect('a', 'ap', 'at', 't', 'u')
            ->where('fa.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('fa.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find found animals by user with all related data
     */
    public function findByUserWithRelations(User $user): array
    {
        return $this->createQueryBuilder('fa')
            ->leftJoin('fa.animalId', 'a')
            ->leftJoin('a.animalPhotos', 'ap')
            ->leftJoin('a.animalTags', 'at')
            ->leftJoin('at.tagId', 't')
            ->addSelect('a', 'ap', 'at', 't')
            ->where('fa.userId = :userId')
            ->setParameter('userId', $user)
            ->orderBy('fa.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find found animal by animal
     */
    public function findByAnimal($animal): ?FoundAnimals
    {
        return $this->findOneBy(['animalId' => $animal]);
    }

    /**
     * Count found animals by user
     */
    public function countByUser(User $user): int
    {
        return $this->count(['userId' => $user]);
    }

    /**
     * Count active (non-archived) found animals by user
     */
    public function countActiveByUser(User $user): int
    {
        return $this->createQueryBuilder('fa')
            ->leftJoin('fa.animalId', 'a')
            ->select('COUNT(fa.id)')
            ->where('fa.userId = :userId')
            ->andWhere('a.status != :archivedStatus')
            ->setParameter('userId', $user)
            ->setParameter('archivedStatus', 'ARCHIVED')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count all found animals by user (including archived)
     */
    public function countAllByUser(User $user): int
    {
        return $this->count(['userId' => $user]);
    }

    /**
     * Find archived found animals by user with all related data
     */
    public function findArchivedByUserWithRelations(User $user): array
    {
        return $this->createQueryBuilder('fa')
            ->leftJoin('fa.animalId', 'a')
            ->leftJoin('a.animalPhotos', 'ap')
            ->leftJoin('a.animalTags', 'at')
            ->leftJoin('at.tagId', 't')
            ->addSelect('a', 'ap', 'at', 't')
            ->where('fa.userId = :userId')
            ->andWhere('a.status = :archivedStatus')
            ->setParameter('userId', $user)
            ->setParameter('archivedStatus', 'ARCHIVED')
            ->orderBy('fa.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
