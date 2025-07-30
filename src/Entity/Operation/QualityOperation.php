<?php

namespace App\Entity\Operation;

use App\Entity\Model;
use App\Entity\Process\QualityProcess;
use App\Repository\Operation\QualityOperationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QualityOperationRepository::class)]
class QualityOperation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $isDone = null;

    #[ORM\ManyToOne(inversedBy: 'qualityOperations')]
    private ?QualityProcess $qualityProcess = null;

    #[ORM\ManyToOne(inversedBy: 'qualityOperation')]
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

    public function getQualityProcess(): ?QualityProcess
    {
        return $this->qualityProcess;
    }

    public function setQualityProcess(?QualityProcess $qualityProcess): static
    {
        $this->qualityProcess = $qualityProcess;

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
