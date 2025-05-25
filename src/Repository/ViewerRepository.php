<?php

namespace App\Repository;

use App\Entity\Viewer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ViewerRepository extends ServiceEntityRepository{
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Viewer::class);
    }

    public function findOneByEmail(string $email): ?Viewer{
        return $this->findOneBy(['viewerMail' => $email]);
    }
}
