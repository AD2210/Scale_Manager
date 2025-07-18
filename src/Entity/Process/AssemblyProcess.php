<?php

namespace App\Entity\Process;

use App\Repository\Process\AssemblyProcessRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssemblyProcessRepository::class)]
class AssemblyProcess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column]
    private ?bool $isSpecific = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $methodLink = null;

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

    public function setIsActive(?bool $isActive): static
    {
        if ($isActive === null) {
            $isActive = false;
        }
        $this->isActive = $isActive;

        return $this;
    }

    public function isSpecific(): ?bool
    {
        return $this->isSpecific;
    }

    public function setIsSpecific(?bool $isSpecific): static
    {
        if ($isSpecific === null) {
            $isSpecific = false;
        }
        $this->isSpecific = $isSpecific;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getMethodLink(): ?string
    {
        return $this->methodLink;
    }

    public function setMethodLink(?string $methodLink): static
    {
        $this->methodLink = $methodLink;

        return $this;
    }
}
