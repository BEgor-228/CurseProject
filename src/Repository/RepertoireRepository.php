<?php

namespace App\Repository;

use App\Entity\Repertoire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RepertoireRepository extends ServiceEntityRepository{
    public function __construct(ManagerRegistry $registry){
        parent::__construct($registry, Repertoire::class);
    }
    public function findByAdministrator(int $administratorId): array{
        return $this->findBy(['administrator' => $administratorId]);
    }
}