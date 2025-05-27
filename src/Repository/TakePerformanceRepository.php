<?php

namespace App\Repository;

use App\Entity\Performance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TakePerformanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Performance::class);
    }

    public function findByFilters(?int $repertoireId, ?string $genre, ?string $date): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.lists', 'l')
            ->leftJoin('l.repertoire', 'r');
        if ($repertoireId) {
            $qb->andWhere('r.repertoireId = :repertoireId')
               ->setParameter('repertoireId', $repertoireId);
        }
        if ($genre) {
            $qb->andWhere('p.performanceGenre = :genre')
               ->setParameter('genre', $genre);
        }
        if ($date) {
            $qb->andWhere('p.performanceData = :date')
               ->setParameter('date', new \DateTime($date));
        }
        return $qb->getQuery()->getResult();
    }

    public function findAllRepertoires(): array
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder()
            ->select('r')
            ->from('App\Entity\Repertoire', 'r')
            ->getQuery();
        return $qb->getResult();
    }

    public function findByRepertoireAndManager(int $repertoireId, int $managerId): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.lists', 'l')
            ->innerJoin('l.repertoire', 'r')
            ->innerJoin('r.manager', 'm')
            ->where('r.repertoireId = :repertoireId')
            ->andWhere('m.managerId = :managerId')
            ->setParameter('repertoireId', $repertoireId)
            ->setParameter('managerId', $managerId)
            ->getQuery()
            ->getResult();
    }

    
}