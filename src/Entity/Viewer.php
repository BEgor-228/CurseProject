<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "Viewer")]
class Viewer{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: "Viewer_ID", type: "integer")]
    private ?int $viewerId = null;

    #[ORM\Column(name: "Viewer_password", type: "string", length: 255)]
    private ?string $viewerPassword = null;

    #[ORM\Column(name: "Viewer_mail", type: "string", length: 40)]
    private ?string $viewerMail = null;

    #[ORM\Column(name: "Viewer_fullname", type: "string", length: 40)]
    private ?string $viewerFullname = null;

    public function getViewerId(): ?int{
        return $this->viewerId;
    }

    public function getViewerPassword(): ?string{
        return $this->viewerPassword;
    }

    public function setViewerPassword(?string $viewerPassword): self{
        $this->viewerPassword = $viewerPassword;
        return $this;
    }

    public function getViewerMail(): ?string{
        return $this->viewerMail;
    }

    public function setViewerMail(?string $viewerMail): self{
        $this->viewerMail = $viewerMail;
        return $this;
    }

    public function getViewerFullname(): ?string{
        return $this->viewerFullname;
    }

    public function setViewerFullname(?string $viewerFullname): self{
        $this->viewerFullname = $viewerFullname;
        return $this;
    }
}