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
    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('fa')
            ->leftJoin('fa.animalId', 'a')
            ->leftJoin('a.animalPhotos', 'ap')
            ->leftJoin('fa.userId', 'u')
            ->addSelect('a', 'ap', 'u')
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


}
