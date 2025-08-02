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
    public function findAllWithRelationsPaginated(int $page = 1, int $limit = 9, array $filters = []): array
    {
        $offset = ($page - 1) * $limit;

        // If location filters are present, use location-based search
        if (!empty($filters['latitude']) && !empty($filters['longitude'])) {
            return $this->findAllWithRelationsPaginatedWithLocation($page, $limit, $filters);
        }

        // Regular search without location - simplified to avoid JOIN issues
        $qb = $this->createQueryBuilder('lp')
            ->leftJoin('lp.animalId', 'a')
            ->addSelect('a')
            ->where('a.status = :status')
            ->setParameter('status', 'LOST')
            ->orderBy('lp.createdAt', 'DESC');

        // Apply filters
        if (!empty($filters['search'])) {
            // First, try to find animals by direct field search
            $qb->andWhere('a.name LIKE :search OR a.description LIKE :search OR a.animalType LIKE :search OR a.age LIKE :search OR lp.lostZone LIKE :search OR lp.lostAddress LIKE :search')
                ->setParameter('search', '%' . $filters['search'] . '%');

            // Also search for animals that have tags matching the search term
            $qb->orWhere('EXISTS (
                SELECT 1 FROM App\Entity\AnimalTags at2
                JOIN at2.tagId t2
                WHERE at2.animalId = a.id
                AND t2.name LIKE :search
            )')
                ->setParameter('search', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['animalType'])) {
            $qb->andWhere('a.animalType = :animalType')
                ->setParameter('animalType', $filters['animalType']);
        }

        // Get the basic results first
        $basicResults = $qb->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        // Now fetch the related data for these results
        $results = [];
        foreach ($basicResults as $lostPet) {
            $qb = $this->createQueryBuilder('lp')
                ->leftJoin('lp.animalId', 'a')
                ->leftJoin('a.animalPhotos', 'ap')
                ->leftJoin('a.animalTags', 'at')
                ->leftJoin('at.tagId', 't')
                ->leftJoin('lp.userId', 'u')
                ->addSelect('a', 'ap', 'at', 't', 'u')
                ->where('lp.id = :id')
                ->setParameter('id', $lostPet->getId());

            $fullResult = $qb->getQuery()->getSingleResult();
            $results[] = $fullResult;
        }

        return $results;
    }

    private function findAllWithRelationsPaginatedWithLocation(int $page = 1, int $limit = 9, array $filters = []): array
    {
        $offset = ($page - 1) * $limit;
        $latitude = (float) $filters['latitude'];
        $longitude = (float) $filters['longitude'];

        error_log("Lost Pets Location search - Latitude: $latitude, Longitude: $longitude, Page: $page, Limit: $limit, Offset: $offset");

        // Build WHERE conditions for other filters
        $whereConditions = ['1=1']; // Always true condition as base
        $parameters = [
            'latitude' => $latitude,
            'longitude' => $longitude
        ];

        if (!empty($filters['search'])) {
            $whereConditions[] = '(a.name LIKE :search OR a.description LIKE :search OR a.animalType LIKE :search OR a.age LIKE :search OR lp.lost_zone LIKE :search OR lp.lost_address LIKE :search OR EXISTS (SELECT 1 FROM animal_tags at2 JOIN tags t2 ON at2.tag_id_id = t2.id WHERE at2.animal_id_id = a.id AND t2.name LIKE :search))';
            $parameters['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['animalType'])) {
            $whereConditions[] = 'a.animalType = :animalType';
            $parameters['animalType'] = $filters['animalType'];
        }

        // Use native SQL to get IDs ordered by distance, then fetch full entities
        $sql = "
            SELECT lp.id, lp.created_at,
                   (
                       6371 * acos(
                           cos(radians(:latitude)) *
                           cos(radians(lp.latitude)) *
                           cos(radians(lp.longitude) - radians(:longitude)) +
                           sin(radians(:latitude)) *
                           sin(radians(lp.latitude))
                       )
                   ) as distance
            FROM lost_pets lp
            LEFT JOIN animals a ON lp.animal_id_id = a.id
            WHERE " . implode(' AND ', $whereConditions) . "
            AND a.status = 'LOST'
            AND lp.latitude IS NOT NULL
            AND lp.longitude IS NOT NULL
            ORDER BY distance ASC, lp.created_at DESC
            LIMIT " . $limit . " OFFSET " . $offset;

        error_log("Lost Pets SQL Query: " . $sql);

        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);

        // Bind parameters
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $stmt->bindValue($key, implode(',', $value), \PDO::PARAM_STR);
            } else {
                $stmt->bindValue($key, $value);
            }
        }

        $result = $stmt->executeQuery();
        $rows = $result->fetchAllAssociative();

        error_log("Lost Pets SQL Results count: " . count($rows));

        if (empty($rows)) {
            return [];
        }

        // Extract IDs and fetch full entities
        $ids = array_column($rows, 'id');

        if (empty($ids)) {
            return [];
        }

        error_log("Lost Pets IDs to fetch: " . implode(', ', $ids));

        $qb = $this->createQueryBuilder('lp')
            ->leftJoin('lp.animalId', 'a')
            ->leftJoin('a.animalPhotos', 'ap')
            ->leftJoin('a.animalTags', 'at')
            ->leftJoin('at.tagId', 't')
            ->leftJoin('lp.userId', 'u')
            ->addSelect('a', 'ap', 'at', 't', 'u')
            ->where('lp.id IN (:ids)')
            ->setParameter('ids', $ids);

        $results = $qb->getQuery()->getResult();

        error_log("Lost Pets DQL Results count: " . count($results));

        // Sort results to maintain the order from the SQL query
        $orderedResults = [];
        foreach ($ids as $id) {
            foreach ($results as $result) {
                if ($result->getId() == $id) {
                    $orderedResults[] = $result;
                    break;
                }
            }
        }

        error_log("Lost Pets Final ordered results count: " . count($orderedResults));

        return $orderedResults;
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
