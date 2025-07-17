<?php

namespace App\Entity\Process;

use App\Repository\Process\Print3DProcessRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: Print3DProcessRepository::class)]
class Print3DProcess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    /**
     * @var Collection<int, TreatmentProcess>
     */
    #[ORM\ManyToMany(targetEntity: TreatmentProcess::class)]
    private Collection $treatmentProcess;

    /**
     * @var Collection<int, FinishProcess>
     */
    #[ORM\ManyToMany(targetEntity: FinishProcess::class)]
    private Collection $finishProcess;

    public function __construct()
    {
        $this->treatmentProcess = new ArrayCollection();
        $this->finishProcess = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, TreatmentProcess>
     */

    public function setTreatmentProcess(array $treatments): static
    {
        $this->treatmentProcess->clear();

        foreach ($treatments as $treatment) {
            if (!$this->treatmentProcess->contains($treatment)) {
                $this->addTreatmentProcess($treatment);
            }
        }

        return $this;
    }

    public function getTreatmentProcess(): Collection
    {
        return $this->treatmentProcess;
    }

    public function addTreatmentProcess(TreatmentProcess $treatmentProcess): static
    {
        if (!$this->treatmentProcess->contains($treatmentProcess)) {
            $this->treatmentProcess->add($treatmentProcess);
        }

        return $this;
    }

    public function removeTreatmentProcess(TreatmentProcess $treatmentProcess): static
    {
        $this->treatmentProcess->removeElement($treatmentProcess);

        return $this;
    }

    /**
     * @return Collection<int, FinishProcess>
     */

    public function setFinishProcess(array $finishes): static
    {
        $this->finishProcess->clear();

        foreach ($finishes as $finish) {
            if (!$this->finishProcess->contains($finish)) {
                $this->addFinishProcess($finish);
            }
        }

        return $this;
    }

    public function getFinishProcess(): Collection
    {
        return $this->finishProcess;
    }

    public function addFinishProcess(FinishProcess $finishProcess): static
    {
        if (!$this->finishProcess->contains($finishProcess)) {
            $this->finishProcess->add($finishProcess);
        }

        return $this;
    }

    public function removeFinishProcess(FinishProcess $finishProcess): static
    {
        $this->finishProcess->removeElement($finishProcess);

        return $this;
    }
}
