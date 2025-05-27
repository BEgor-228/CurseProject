<?php
namespace App\Repository;

use App\Entity\Hall;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class HallRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry){
        parent::__construct($registry, Hall::class);
    }
}