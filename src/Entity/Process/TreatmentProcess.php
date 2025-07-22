<?php

namespace App\Entity\Process;

use App\Entity\Preset\TreatmentPreset;
use App\Repository\Process\TreatmentProcessRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TreatmentProcessRepository::class)]
class TreatmentProcess
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
     * @var Collection<int, TreatmentPreset>
     */
    #[ORM\ManyToMany(targetEntity: TreatmentPreset::class, mappedBy: 'treatmentProcesses')]
    private Collection $treatmentPresets;

    public function __construct()
    {
        $this->treatmentPresets = new ArrayCollection();
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
     * @return Collection<int, TreatmentPreset>
     */
    public function getTreatmentPresets(): Collection
    {
        return $this->treatmentPresets;
    }

    public function addTreatmentPreset(TreatmentPreset $treatmentPreset): static
    {
        if (!$this->treatmentPresets->contains($treatmentPreset)) {
            $this->treatmentPresets->add($treatmentPreset);
            $treatmentPreset->addTreatmentProcess($this);
        }

        return $this;
    }

    public function removeTreatmentPreset(TreatmentPreset $treatmentPreset): static
    {
        if ($this->treatmentPresets->removeElement($treatmentPreset)) {
            $treatmentPreset->removeTreatmentProcess($this);
        }

        return $this;
    }
}
