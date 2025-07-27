<?php

namespace App\Entity\Process;

use App\Entity\Preset\Print3DPreset;
use App\Repository\Process\Print3DMaterialRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: Print3DMaterialRepository::class)]
class Print3DMaterial
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

    /**
     * @var Collection<int, Print3DPreset>
     */
    #[ORM\OneToMany(targetEntity: Print3DPreset::class, mappedBy: 'print3dMaterial')]
    private Collection $print3DPresets;

    public function __construct()
    {
        $this->treatmentProcess = new ArrayCollection();
        $this->finishProcess = new ArrayCollection();
        $this->print3DPresets = new ArrayCollection();
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
    public function getTreatmentProcess(): Collection
    {
        return $this->treatmentProcess;
    }

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

    /**
     * @return Collection<int, Print3DPreset>
     */
    public function getPrint3DPresets(): Collection
    {
        return $this->print3DPresets;
    }

    public function addPrint3DPreset(Print3DPreset $print3DPreset): static
    {
        if (!$this->print3DPresets->contains($print3DPreset)) {
            $this->print3DPresets->add($print3DPreset);
            $print3DPreset->setPrint3dMaterial($this);
        }

        return $this;
    }

    public function removePrint3DPreset(Print3DPreset $print3DPreset): static
    {
        if ($this->print3DPresets->removeElement($print3DPreset)) {
            // set the owning side to null (unless already changed)
            if ($print3DPreset->getPrint3dMaterial() === $this) {
                $print3DPreset->setPrint3dMaterial(null);
            }
        }

        return $this;
    }
}
