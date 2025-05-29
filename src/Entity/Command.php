<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Command")]
class Command{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "Command_ID", type: "integer")]
    private ?int $commandId = null;

    #[ORM\Column(name: "Command_content", type: "string", length: 1000)]
    private ?string $commandContent = null;

    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    #[ORM\JoinColumn(name: "Administrator_ID", referencedColumnName: "Administrator_ID", nullable: false)]
    private ?Administrator $administrator = null;

    #[ORM\ManyToOne(targetEntity: Manager::class)]
    #[ORM\JoinColumn(name: "Manager_ID", referencedColumnName: "Manager_ID", nullable: false)]
    private ?Manager $manager = null;

    public function getCommandId(): ?int{
        return $this->commandId;
    }

    public function getCommandContent(): ?string{
        return $this->commandContent;
    }

    public function setCommandContent(?string $commandContent): self{
        $this->commandContent = $commandContent;
        return $this;
    }

    public function getAdministrator(): ?Administrator{
        return $this->administrator;
    }

    public function setAdministrator(?Administrator $administrator): self{
        $this->administrator = $administrator;
        return $this;
    }

    public function getManager(): ?Manager{
        return $this->manager;
    }

    public function setManager(?Manager $manager): self{
        $this->manager = $manager;
        return $this;
    }
}