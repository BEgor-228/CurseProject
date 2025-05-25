<?php

namespace App\Repository;

use App\Entity\Administrator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdministratorRepository extends ServiceEntityRepository{
    public function __construct(ManagerRegistry $registry){
        parent::__construct($registry, Administrator::class);
    }
    public function findOneByEmail(string $email): ?Administrator{
        return $this->findOneBy(['administratorMail' => $email]);
    }
    public function findAll(): array{
        return $this->findBy([]);
    }
}