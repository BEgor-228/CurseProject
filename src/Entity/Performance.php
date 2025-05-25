<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "Performance")]
class Performance{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "Performance_ID", type: "integer")]
    private ?int $performanceId = null;

    #[ORM\Column(name: "Performance_description", type: "string", length: 300)]
    private ?string $performanceDescription = null;

    #[ORM\Column(name: "Performance_castList", type: "string", length: 300)]
    private ?string $performanceCastList = null;

    #[ORM\Column(name: "Performance_duration", type: "time")]
    private ?\DateTimeInterface $performanceDuration = null;

    #[ORM\Column(name: "Performance_data", type: "date")]
    private ?\DateTimeInterface $performanceData = null;

    #[ORM\Column(name: "Performance_title", type: "string", length: 30)]
    private ?string $performanceTitle = null;

    #[ORM\Column(name: "Performance_price", type: "float")]
    private ?float $performancePrice = null;

    #[ORM\Column(name: "Performance_genre", type: "string", length: 15)]
    private ?string $performanceGenre = null;

    #[ORM\ManyToOne(targetEntity: Hall::class)]
    #[ORM\JoinColumn(name: "Hall_ID", referencedColumnName: "Hall_ID", nullable: false)]
    private ?Hall $hall = null;

    #[ORM\ManyToOne(targetEntity: Manager::class)]
    #[ORM\JoinColumn(name: "Manager_ID", referencedColumnName: "Manager_ID", nullable: false)]
    private ?Manager $manager = null;

    #[ORM\OneToMany(targetEntity: ListEntity::class, mappedBy: "performance")]
    private Collection $lists;

    public function __construct(){$this->lists = new ArrayCollection();}
    public function getPerformanceId(): ?int{return $this->performanceId;}
    public function getPerformanceDescription(): ?string{return $this->performanceDescription;}
    public function setPerformanceDescription(?string $performanceDescription): self{ $this->performanceDescription = $performanceDescription;return $this;}
    public function getPerformanceCastList(): ?string{return $this->performanceCastList;}
    public function setPerformanceCastList(?string $performanceCastList): self{$this->performanceCastList = $performanceCastList;return $this;}
    public function getPerformanceDuration(): ?\DateTimeInterface{return $this->performanceDuration;}
    public function setPerformanceDuration(?\DateTimeInterface $performanceDuration): self{$this->performanceDuration = $performanceDuration;return $this;}
    public function getPerformanceData(): ?\DateTimeInterface{return $this->performanceData;}
    public function setPerformanceData(?\DateTimeInterface $performanceData): self{$this->performanceData = $performanceData;return $this;}
    public function getPerformanceTitle(): ?string{return $this->performanceTitle;}
    public function setPerformanceTitle(?string $performanceTitle): self{$this->performanceTitle = $performanceTitle;return $this;}
    public function getPerformancePrice(): ?float{return $this->performancePrice;}
    public function setPerformancePrice(?float $performancePrice): self{$this->performancePrice = $performancePrice;return $this;}
    public function getPerformanceGenre(): ?string{return $this->performanceGenre;}
    public function setPerformanceGenre(?string $performanceGenre): self{$this->performanceGenre = $performanceGenre;return $this;}
    public function getHall(): ?Hall{return $this->hall;}
    public function setHall(?Hall $hall): self{$this->hall = $hall;return $this;}
    public function getManager(): ?Manager{return $this->manager;}
    public function setManager(?Manager $manager): self{$this->manager = $manager;return $this;}

    public function getLists(): Collection{return $this->lists;}
    public function addList(ListEntity $list): self{
        if (!$this->lists->contains($list)) {
            $this->lists[] = $list;
            $list->setPerformance($this);
        }
        return $this;
    }

    public function removeList(ListEntity $list): self{
        if ($this->lists->removeElement($list)) {
            if ($list->getPerformance() === $this) {
                $list->setPerformance(null);
            }
        }
        return $this;
    }
}