<?php

namespace App\Repository;

use App\Entity\Animals;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Animals>
 */
class AnimalsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Animals::class);
    }

    /**
     * Find animals by type (uses idx_animal_type index)
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.animalType = :type')
            ->setParameter('type', $type)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find animals by gender (uses idx_animal_gender index)
     */
    public function findByGender(string $gender): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.gender = :gender')
            ->setParameter('gender', $gender)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find animals by size (uses idx_animal_size index)
     */
    public function findBySize(string $size): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.size = :size')
            ->setParameter('size', $size)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find animals by color (uses idx_animal_color index)
     */
    public function findByColor(string $color): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.color = :color')
            ->setParameter('color', $color)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find animals created after a specific date (uses idx_animal_created_at index)
     */
    public function findCreatedAfter(\DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.createdAt >= :date')
            ->setParameter('date', $date)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find animals updated after a specific date (uses idx_animal_updated_at index)
     */
    public function findUpdatedAfter(\DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.updatedAt >= :date')
            ->setParameter('date', $date)
            ->orderBy('a.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Advanced search with multiple filters (leverages multiple indexes)
     */
    public function searchAnimals(
        ?string $type = null,
        ?string $gender = null,
        ?string $size = null,
        ?string $color = null,
        ?\DateTimeImmutable $createdAfter = null
    ): array {
        $qb = $this->createQueryBuilder('a');

        if ($type) {
            $qb->andWhere('a.animalType = :type')
                ->setParameter('type', $type);
        }

        if ($gender) {
            $qb->andWhere('a.gender = :gender')
                ->setParameter('gender', $gender);
        }

        if ($size) {
            $qb->andWhere('a.size = :size')
                ->setParameter('size', $size);
        }

        if ($color) {
            $qb->andWhere('a.color = :color')
                ->setParameter('color', $color);
        }

        if ($createdAfter) {
            $qb->andWhere('a.createdAt >= :createdAfter')
                ->setParameter('createdAfter', $createdAfter);
        }

        return $qb->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Animals[] Returns an array of Animals objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Animals
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
