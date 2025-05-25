<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Place")]
class Place
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "Place_ID", type: "integer")]
    private ?int $placeId = null;

    #[ORM\Column(name: "Place_number", type: "integer")]
    private ?int $placeNumber = null;

    #[ORM\Column(name: "Place_level", type: "integer")]
    private ?int $placeLevel = null;

    #[ORM\Column(name: "Place_status", type: "string", length: 15)]
    private ?string $placeStatus = null;

    #[ORM\ManyToOne(targetEntity: Hall::class)]
    #[ORM\JoinColumn(name: "Hall_ID", referencedColumnName: "Hall_ID", nullable: false)]
    private ?Hall $hall = null;

    public function getPlaceId(): ?int
    {
        return $this->placeId;
    }

    public function getPlaceNumber(): ?int
    {
        return $this->placeNumber;
    }

    public function setPlaceNumber(?int $placeNumber): self
    {
        $this->placeNumber = $placeNumber;
        return $this;
    }

    public function getPlaceLevel(): ?int
    {
        return $this->placeLevel;
    }

    public function setPlaceLevel(?int $placeLevel): self
    {
        $this->placeLevel = $placeLevel;
        return $this;
    }

    public function getPlaceStatus(): ?string
    {
        return $this->placeStatus;
    }

    public function setPlaceStatus(?string $placeStatus): self
    {
        $this->placeStatus = $placeStatus;
        return $this;
    }

    public function getHall(): ?Hall
    {
        return $this->hall;
    }

    public function setHall(?Hall $hall): self
    {
        $this->hall = $hall;
        return $this;
    }
}