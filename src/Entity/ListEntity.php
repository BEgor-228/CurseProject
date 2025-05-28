<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "List")]
class ListEntity{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "List_ID", type: "integer")]
    private ?int $listId = null;

    #[ORM\ManyToOne(targetEntity: Performance::class)]
    #[ORM\JoinColumn(name: "Performance_ID", referencedColumnName: "Performance_ID", nullable: false)]
    private ?Performance $performance = null;

    #[ORM\ManyToOne(targetEntity: Repertoire::class)]
    #[ORM\JoinColumn(name: "Repertoire_ID", referencedColumnName: "Repertoire_ID", nullable: false)]
    private ?Repertoire $repertoire = null;

    public function getListId(): ?int{
        return $this->listId;
    }

    public function getPerformance(): ?Performance{
        return $this->performance;
    }

    public function setPerformance(?Performance $performance): self{
        $this->performance = $performance;
        return $this;
    }

    public function getRepertoire(): ?Repertoire{
        return $this->repertoire;
    }

    public function setRepertoire(?Repertoire $repertoire): self{
        $this->repertoire = $repertoire;
        return $this;
    }
}