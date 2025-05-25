<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Hall")]
class Hall
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "Hall_ID", type: "integer")]
    private ?int $hallId = null;

    #[ORM\Column(name: "Hall_size", type: "string", length: 15, nullable: true)]
    private ?string $hallSize = null;

    #[ORM\Column(name: "Hall_capacity", type: "integer")]
    private ?int $hallCapacity = null;

    // Геттеры и сеттеры
    public function getHallId(): ?int
    {
        return $this->hallId;
    }

    public function getHallSize(): ?string
    {
        return $this->hallSize;
    }

    public function setHallSize(?string $hallSize): self
    {
        $this->hallSize = $hallSize;
        return $this;
    }

    public function getHallCapacity(): ?int
    {
        return $this->hallCapacity;
    }

    public function setHallCapacity(?int $hallCapacity): self
    {
        $this->hallCapacity = $hallCapacity;
        return $this;
    }
}