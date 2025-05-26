<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Hall")]
class Hall{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "Hall_ID", type: "integer")]
    private ?int $hallId = null;

    #[ORM\Column(name: "Hall_capacity", type: "integer")]
    private ?int $hallCapacity = null;

    public function getHallId(): ?int{
        return $this->hallId;
    }

    public function getHallCapacity(): ?int{
        return $this->hallCapacity;
    }

    public function setHallCapacity(?int $hallCapacity): self{
        $this->hallCapacity = $hallCapacity;
        return $this;
    }
}