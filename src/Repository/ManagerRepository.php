<?php

namespace App\Repository;

use App\Entity\Manager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ManagerRepository extends ServiceEntityRepository{
    public function __construct(ManagerRegistry $registry){
        parent::__construct($registry, Manager::class);
    }
    public function findOneByEmail(string $email): ?Manager{
        return $this->findOneBy(['managerMail' => $email]);
    }
}