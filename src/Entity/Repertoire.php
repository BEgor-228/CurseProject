<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Repertoire")]
class Repertoire{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "Repertoire_ID", type: "integer")]
    private ?int $repertoireId = null;

    #[ORM\Column(name: "Repertoire_title", type: "string", length: 30)]
    private ?string $repertoireTitle = null;

    #[ORM\Column(name: "Repertoire_size", type: "integer")]
    private ?int $repertoireSize = null;

    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    #[ORM\JoinColumn(name: "Administrator_ID", referencedColumnName: "Administrator_ID", nullable: false)]
    private ?Administrator $administrator = null;

    #[ORM\ManyToOne(targetEntity: Manager::class)]
    #[ORM\JoinColumn(name: "Manager_ID", referencedColumnName: "Manager_ID", nullable: true)]
    private ?Manager $manager = null;

    public function getRepertoireId(): ?int{
        return $this->repertoireId;
    }

    public function getRepertoireTitle(): ?string{
        return $this->repertoireTitle;
    }

    public function setRepertoireTitle(?string $repertoireTitle): self{
        $this->repertoireTitle = $repertoireTitle;
        return $this;
    }

    public function getRepertoireSize(): ?int{
        return $this->repertoireSize;
    }

    public function setRepertoireSize(?int $repertoireSize): self{
        $this->repertoireSize = $repertoireSize;
        return $this;
    }

    public function getAdministrator(): ?Administrator{
        return $this->administrator;
    }

    public function setAdministrator(?Administrator $administrator): self{
        $this->administrator = $administrator;
        return $this;
    }

    public function getManager(): ?Manager {
        return $this->manager;
    }
    
    public function setManager(?Manager $manager): self {
        $this->manager = $manager;
        return $this;
    }
}