<?php

namespace App\Entity\Base;

use App\Repository\Base\SlicerProfilRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SlicerProfilRepository::class)]
class SlicerProfil
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fileLink = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getFileLink(): ?string
    {
        return $this->fileLink;
    }

    public function setFileLink(?string $fileLink): static
    {
        $this->fileLink = $fileLink;

        return $this;
    }
}
