<?php

namespace App\Repository;

use App\Entity\Tags;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tags>
 */
class TagsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tags::class);
    }

    /**
     * Find a tag by name (case insensitive)
     */
    public function findByName(string $name): ?Tags
    {
        return $this->createQueryBuilder('t')
            ->andWhere('LOWER(t.name) = LOWER(:name)')
            ->andWhere('t.isActive = :isActive')
            ->setParameter('name', $name)
            ->setParameter('isActive', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find or create a tag by name
     */
    public function findOrCreateByName(string $name): Tags
    {
        $tag = $this->findByName($name);

        if (!$tag) {
            $tag = new Tags();
            $tag->setName(trim($name));
            $tag->setIsActive(true);
            $tag->setCreatedAt(new \DateTimeImmutable());
        }

        return $tag;
    }

    /**
     * Get the 10 most used tags
     */
    public function findMostUsedTags(int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t', 'COUNT(at.id) as usage_count')
            ->leftJoin('App\Entity\AnimalTags', 'at', 'WITH', 'at.tagId = t.id')
            ->where('t.isActive = :isActive')
            ->setParameter('isActive', true)
            ->groupBy('t.id')
            ->orderBy('usage_count', 'DESC')
            ->addOrderBy('t.name', 'ASC')
            ->setMaxResults($limit);

        $results = $qb->getQuery()->getResult();

        // Extract just the Tags entities
        return array_map(function ($result) {
            return $result[0]; // The Tags entity is at index 0
        }, $results);
    }

    //    /**
    //     * @return Tags[] Returns an array of Tags objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Tags
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
