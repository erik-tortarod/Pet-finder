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
    public function findAllWithRelationsPaginated(int $page = 1, int $limit = 9, array $filters = []): array
    {
        $offset = ($page - 1) * $limit;

        // If location filters are present, use location-based search
        if (!empty($filters['latitude']) && !empty($filters['longitude'])) {
            return $this->findAllWithRelationsPaginatedWithLocation($page, $limit, $filters);
        }

        // Regular search without location - simplified to avoid JOIN issues
        $qb = $this->createQueryBuilder('fa')
            ->leftJoin('fa.animalId', 'a')
            ->addSelect('a')
            ->orderBy('fa.createdAt', 'DESC');

        // Apply filters
        if (!empty($filters['search'])) {
            $qb->andWhere('a.name LIKE :search OR a.description LIKE :search OR fa.foundZone LIKE :search OR fa.foundAddress LIKE :search')
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
        foreach ($basicResults as $foundAnimal) {
            $qb = $this->createQueryBuilder('fa')
                ->leftJoin('fa.animalId', 'a')
                ->leftJoin('a.animalPhotos', 'ap')
                ->leftJoin('a.animalTags', 'at')
                ->leftJoin('at.tagId', 't')
                ->leftJoin('fa.userId', 'u')
                ->addSelect('a', 'ap', 'at', 't', 'u')
                ->where('fa.id = :id')
                ->setParameter('id', $foundAnimal->getId());

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

        error_log("Location search - Latitude: $latitude, Longitude: $longitude, Page: $page, Limit: $limit, Offset: $offset");

        // Build WHERE conditions for other filters
        $whereConditions = ['1=1']; // Always true condition as base
        $parameters = [
            'latitude' => $latitude,
            'longitude' => $longitude
        ];

        if (!empty($filters['search'])) {
            $whereConditions[] = '(a.name LIKE :search OR a.description LIKE :search OR fa.foundZone LIKE :search OR fa.foundAddress LIKE :search)';
            $parameters['search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['animalType'])) {
            $whereConditions[] = 'a.animalType = :animalType';
            $parameters['animalType'] = $filters['animalType'];
        }

        // Use native SQL to get IDs ordered by distance, then fetch full entities
        $sql = "
            SELECT fa.id, fa.created_at,
                   (
                       6371 * acos(
                           cos(radians(:latitude)) *
                           cos(radians(fa.latitude)) *
                           cos(radians(fa.longitude) - radians(:longitude)) +
                           sin(radians(:latitude)) *
                           sin(radians(fa.latitude))
                       )
                   ) as distance
            FROM found_animals fa
            LEFT JOIN animals a ON fa.animal_id_id = a.id
            WHERE " . implode(' AND ', $whereConditions) . "
            AND fa.latitude IS NOT NULL
            AND fa.longitude IS NOT NULL
            ORDER BY distance ASC, fa.created_at DESC
            LIMIT " . $limit . " OFFSET " . $offset;

        error_log("SQL Query: " . $sql);

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

        error_log("SQL Results count: " . count($rows));

        if (empty($rows)) {
            return [];
        }

        // Extract IDs and fetch full entities
        $ids = array_column($rows, 'id');

        if (empty($ids)) {
            return [];
        }

        error_log("IDs to fetch: " . implode(', ', $ids));

        $qb = $this->createQueryBuilder('fa')
            ->leftJoin('fa.animalId', 'a')
            ->leftJoin('a.animalPhotos', 'ap')
            ->leftJoin('a.animalTags', 'at')
            ->leftJoin('at.tagId', 't')
            ->leftJoin('fa.userId', 'u')
            ->addSelect('a', 'ap', 'at', 't', 'u')
            ->where('fa.id IN (:ids)')
            ->setParameter('ids', $ids);

        $results = $qb->getQuery()->getResult();

        error_log("DQL Results count: " . count($results));

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

        error_log("Final ordered results count: " . count($orderedResults));

        return $orderedResults;
    }

    /**
     * Find found animals by proximity to a given location using native SQL
     */
    public function findByProximity(float $latitude, float $longitude, float $radiusKm = 10, int $limit = 50): array
    {
        $sql = "
            SELECT fa.*, a.*, u.*
            FROM found_animals fa
            LEFT JOIN animals a ON fa.animal_id_id = a.id
            LEFT JOIN user u ON fa.user_id_id = u.id
            WHERE a.status = 'FOUND'
            AND fa.latitude IS NOT NULL
            AND fa.longitude IS NOT NULL
            AND (
                6371 * acos(
                    cos(radians(:latitude)) *
                    cos(radians(fa.latitude)) *
                    cos(radians(fa.longitude) - radians(:longitude)) +
                    sin(radians(:latitude)) *
                    sin(radians(fa.latitude))
                )
            ) <= :radius
            ORDER BY fa.created_at DESC
            LIMIT :limit
        ";

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('latitude', $latitude);
        $stmt->bindValue('longitude', $longitude);
        $stmt->bindValue('radius', $radiusKm);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);

        $result = $stmt->executeQuery();

        // Convert raw data to entities
        $foundAnimals = [];
        while ($row = $result->fetchAssociative()) {
            $foundAnimal = $this->find($row['id']);
            if ($foundAnimal) {
                $foundAnimals[] = $foundAnimal;
            }
        }

        return $foundAnimals;
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
