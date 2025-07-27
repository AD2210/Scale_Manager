<?php

namespace App\Entity\Operation;

use App\Entity\Model;
use App\Entity\Process\TreatmentProcess;
use App\Repository\Operation\TreatmentOperationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TreatmentOperationRepository::class)]
class TreatmentOperation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private bool $isDone = false;

    #[ORM\ManyToOne]
    private ?TreatmentProcess $treatmentProcess = null;

    #[ORM\ManyToOne(inversedBy: 'treatmentOperation')]
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

    public function getTreatmentProcess(): ?TreatmentProcess
    {
        return $this->treatmentProcess;
    }

    public function setTreatmentProcess(?TreatmentProcess $treatmentProcess): static
    {
        $this->treatmentProcess = $treatmentProcess;

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
