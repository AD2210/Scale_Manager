<?php

namespace App\Entity\Operation;

use App\Entity\Model;
use App\Entity\Process\FinishProcess;
use App\Repository\Operation\FinishOperationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FinishOperationRepository::class)]
class FinishOperation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private bool $isDone = false;

    #[ORM\ManyToOne]
    private ?FinishProcess $finishProcess = null;

    #[ORM\ManyToOne(inversedBy: 'finishOperation')]
    private ?Model $model = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isDone(): bool
    {
        return $this->isDone;
    }

    public function setIsDone(bool $isDone): static
    {
        $this->isDone = $isDone;

        return $this;
    }

    public function getFinishProcess(): ?FinishProcess
    {
        return $this->finishProcess;
    }

    public function setFinishProcess(?FinishProcess $finishProcess): static
    {
        $this->finishProcess = $finishProcess;

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
