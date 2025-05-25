<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Manager")]
class Manager
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "Manager_ID", type: "integer")]
    private ?int $managerId = null;

    #[ORM\Column(name: "Manager_password", type: "string", length: 255)]
    private ?string $managerPassword = null;

    #[ORM\Column(name: "Manager_mail", type: "string", length: 40)]
    private ?string $managerMail = null;

    #[ORM\Column(name: "Manager_fullname", type: "string", length: 40)]
    private ?string $managerFullname = null;

    #[ORM\ManyToOne(targetEntity: Administrator::class)]
    #[ORM\JoinColumn(name: "Administrator_ID", referencedColumnName: "Administrator_ID", nullable: false)]
    private ?Administrator $administrator = null;

    public function getManagerId(): ?int
    {
        return $this->managerId;
    }

    public function getManagerPassword(): ?string
    {
        return $this->managerPassword;
    }

    public function setManagerPassword(?string $managerPassword): self
    {
        $this->managerPassword = $managerPassword;
        return $this;
    }

    public function getManagerMail(): ?string
    {
        return $this->managerMail;
    }

    public function setManagerMail(?string $managerMail): self
    {
        $this->managerMail = $managerMail;
        return $this;
    }

    public function getManagerFullname(): ?string
    {
        return $this->managerFullname;
    }

    public function setManagerFullname(?string $managerFullname): self
    {
        $this->managerFullname = $managerFullname;
        return $this;
    }

    public function getAdministrator(): ?Administrator
    {
        return $this->administrator;
    }

    public function setAdministrator(?Administrator $administrator): self
    {
        $this->administrator = $administrator;
        return $this;
    }
}