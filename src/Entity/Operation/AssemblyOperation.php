<?php

namespace App\Entity\Operation;

use App\Entity\Model;
use App\Entity\Process\AssemblyProcess;
use App\Repository\Operation\AssemblyOperationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssemblyOperationRepository::class)]
class AssemblyOperation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $isDone = null;

    #[ORM\ManyToOne(inversedBy: 'assemblyOperations')]
    private ?AssemblyProcess $assemblyProcess = null;

    #[ORM\ManyToOne(inversedBy: 'assemblyOperation')]
    private ?Model $model = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isDone(): ?bool
    {
        return $this->isDone;
    }

    public function setIsDone(bool $isDone): static
    {
        $this->isDone = $isDone;

        return $this;
    }

    public function getAssemblyProcess(): ?AssemblyProcess
    {
        return $this->assemblyProcess;
    }

    public function setAssemblyProcess(?AssemblyProcess $assemblyProcess): static
    {
        $this->assemblyProcess = $assemblyProcess;

        return $this;
    }

    public function getModel(): ?Model
    {
        return $this->model;
    }

    public function setModel(?Model $model): static
    {
        $this->model = $model;

        return $this;
    }
}
