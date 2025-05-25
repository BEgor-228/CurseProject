<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Administrator")]
class Administrator
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "Administrator_ID", type: "integer")]
    private ?int $administratorId = null;

    #[ORM\Column(name: "Administrator_password", type: "string", length: 255)]
    private ?string $administratorPassword = null;

    #[ORM\Column(name: "Administrator_mail", type: "string", length: 40)]
    private ?string $administratorMail = null;

    #[ORM\Column(name: "Administrator_fullname", type: "string", length: 40)]
    private ?string $administratorFullname = null;

    public function getAdministratorId(): ?int
    {
        return $this->administratorId;
    }

    public function getAdministratorPassword(): ?string
    {
        return $this->administratorPassword;
    }

    public function setAdministratorPassword(?string $administratorPassword): self
    {
        $this->administratorPassword = $administratorPassword;
        return $this;
    }

    public function getAdministratorMail(): ?string
    {
        return $this->administratorMail;
    }

    public function setAdministratorMail(?string $administratorMail): self
    {
        $this->administratorMail = $administratorMail;
        return $this;
    }

    public function getAdministratorFullname(): ?string
    {
        return $this->administratorFullname;
    }

    public function setAdministratorFullname(?string $administratorFullname): self
    {
        $this->administratorFullname = $administratorFullname;
        return $this;
    }
}