<?php

namespace App\Repository;

use App\Entity\Place;
use App\Entity\Hall;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PlaceRepository extends ServiceEntityRepository{
    public function __construct(ManagerRegistry $registry){
        parent::__construct($registry, Place::class);
    }
    public function findOneByHallAndNumber(Hall $hall, int $placeNumber): ?Place{
        return $this->findOneBy(['hall' => $hall, 'placeNumber' => $placeNumber]);
    }
}