<?php

namespace App\Repository;

use App\Entity\LostPets;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LostPets>
 */
class LostPetsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LostPets::class);
    }

    /**
     * Find all lost pets with all related data (animals, photos, tags, users) - paginated for infinite scroll
     */
    public function findAllWithRelationsPaginated(int $page = 1, int $limit = 10, array $filters = []): array
    {
        // Build the base query with filters
        $qb = $this->createQueryBuilder('lp')
            ->leftJoin('lp.animalId', 'a')
            ->leftJoin('a.animalPhotos', 'ap')
            ->leftJoin('a.animalTags', 'at')
            ->leftJoin('at.tagId', 't')
            ->leftJoin('lp.userId', 'u')
            ->where('a.status = :status')
            ->setParameter('status', 'LOST');

        // Apply search filter
        if (!empty($filters['search'])) {
            $qb->andWhere('(a.name LIKE :search OR a.description LIKE :search OR lp.lostCircumstances LIKE :search)')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }

        // Apply animal type filter
        if (!empty($filters['animalType'])) {
            $qb->andWhere('a.animalType = :animalType')
                ->setParameter('animalType', $filters['animalType']);
        }

        // Apply tags filter
        if (!empty($filters['tags'])) {
            $qb->andWhere('t.name IN (:tags)')
                ->setParameter('tags', $filters['tags']);
        }

        // First, get the lost pets IDs with pagination
        $lostPetIds = (clone $qb)
            ->select('lp.id')
            ->groupBy('lp.id')
            ->orderBy('lp.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        if (empty($lostPetIds)) {
            return [];
        }

        $ids = array_column($lostPetIds, 'id');

        // Then, get the complete data for those IDs
        return $this->createQueryBuilder('lp')
            ->leftJoin('lp.animalId', 'a')
            ->leftJoin('a.animalPhotos', 'ap')
            ->leftJoin('a.animalTags', 'at')
            ->leftJoin('at.tagId', 't')
            ->leftJoin('lp.userId', 'u')
            ->addSelect('a', 'ap', 'at', 't', 'u')
            ->where('lp.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('lp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all lost pets with all related data (animals, photos, tags, users)
     */
    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('lp')
            ->leftJoin('lp.animalId', 'a')
            ->leftJoin('a.animalPhotos', 'ap')
            ->leftJoin('a.animalTags', 'at')
            ->leftJoin('at.tagId', 't')
            ->leftJoin('lp.userId', 'u')
            ->addSelect('a', 'ap', 'at', 't', 'u')
            ->groupBy('lp.id')
            ->orderBy('lp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find lost pets by user with all related data
     */
    public function findByUserWithRelations(User $user): array
    {
        return $this->createQueryBuilder('lp')
            ->leftJoin('lp.animalId', 'a')
            ->leftJoin('a.animalPhotos', 'ap')
            ->leftJoin('a.animalTags', 'at')
            ->leftJoin('at.tagId', 't')
            ->addSelect('a', 'ap', 'at', 't')
            ->where('lp.userId = :userId')
            ->setParameter('userId', $user)
            ->orderBy('lp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find lost pet by animal
     */
    public function findByAnimal($animal): ?LostPets
    {
        return $this->findOneBy(['animalId' => $animal]);
    }

    /**
     * Count lost pets by user
     */
    public function countByUser(User $user): int
    {
        return $this->count(['userId' => $user]);
    }

    /**
     * Count active (non-archived) lost pets by user
     */
    public function countActiveByUser(User $user): int
    {
        return $this->createQueryBuilder('lp')
            ->leftJoin('lp.animalId', 'a')
            ->select('COUNT(lp.id)')
            ->where('lp.userId = :userId')
            ->andWhere('a.status != :archivedStatus')
            ->setParameter('userId', $user)
            ->setParameter('archivedStatus', 'ARCHIVED')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count all lost pets by user (including archived)
     */
    public function countAllByUser(User $user): int
    {
        return $this->count(['userId' => $user]);
    }

    /**
     * Find archived lost pets by user with all related data
     */
    public function findArchivedByUserWithRelations(User $user): array
    {
        return $this->createQueryBuilder('lp')
            ->leftJoin('lp.animalId', 'a')
            ->leftJoin('a.animalPhotos', 'ap')
            ->leftJoin('a.animalTags', 'at')
            ->leftJoin('at.tagId', 't')
            ->addSelect('a', 'ap', 'at', 't')
            ->where('lp.userId = :userId')
            ->andWhere('a.status = :archivedStatus')
            ->setParameter('userId', $user)
            ->setParameter('archivedStatus', 'ARCHIVED')
            ->orderBy('lp.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
