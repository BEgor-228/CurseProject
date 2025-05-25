<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Ticket")]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "Ticket_ID", type: "integer")]
    private ?int $ticketId = null;

    #[ORM\Column(name: "Ticket_purchaseDate", type: "date")]
    private ?\DateTimeInterface $ticketPurchaseDate = null;

    #[ORM\Column(name: "Ticket_hallNumber", type: "integer")]
    private ?int $ticketHallNumber = null;

    #[ORM\ManyToOne(targetEntity: Viewer::class)]
    #[ORM\JoinColumn(name: "Viewer_ID", referencedColumnName: "Viewer_ID", nullable: false)]
    private ?Viewer $viewer = null;

    #[ORM\ManyToOne(targetEntity: Hall::class)]
    #[ORM\JoinColumn(name: "Hall_ID", referencedColumnName: "Hall_ID", nullable: false)]
    private ?Hall $hall = null;

    #[ORM\ManyToOne(targetEntity: Place::class)]
    #[ORM\JoinColumn(name: "Place_ID", referencedColumnName: "Place_ID", nullable: false)]
    private ?Place $place = null;

    #[ORM\ManyToOne(targetEntity: Performance::class)]
    #[ORM\JoinColumn(name: "Performance_ID", referencedColumnName: "Performance_ID", nullable: false)]
    private ?Performance $performance = null;

    public function getTicketId(): ?int
    {
        return $this->ticketId;
    }

    public function getTicketPurchaseDate(): ?\DateTimeInterface
    {
        return $this->ticketPurchaseDate;
    }

    public function setTicketPurchaseDate(?\DateTimeInterface $ticketPurchaseDate): self
    {
        $this->ticketPurchaseDate = $ticketPurchaseDate;
        return $this;
    }

    public function getTicketHallNumber(): ?int
    {
        return $this->ticketHallNumber;
    }

    public function setTicketHallNumber(?int $ticketHallNumber): self
    {
        $this->ticketHallNumber = $ticketHallNumber;
        return $this;
    }

    public function getViewer(): ?Viewer
    {
        return $this->viewer;
    }

    public function setViewer(?Viewer $viewer): self
    {
        $this->viewer = $viewer;
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

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): self
    {
        $this->place = $place;
        return $this;
    }

    public function getPerformance(): ?Performance
    {
        return $this->performance;
    }

    public function setPerformance(?Performance $performance): self
    {
        $this->performance = $performance;
        return $this;
    }
}