<?php

namespace App\Entity\Preset;

use App\Repository\Preset\GlobalPresetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GlobalPresetRepository::class)]
class GlobalPreset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\ManyToOne(inversedBy: 'globalPresets')]
    private ?Print3DPreset $print3dPreset = null;

    #[ORM\ManyToOne(inversedBy: 'globalPresets')]
    private ?TreatmentPreset $treatmentPreset = null;

    #[ORM\ManyToOne(inversedBy: 'globalPresets')]
    private ?FinishPreset $finishPreset = null;

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

    public function getPrint3dPreset(): ?Print3DPreset
    {
        return $this->print3dPreset;
    }

    public function setPrint3dPreset(?Print3DPreset $print3dPreset): static
    {
        $this->print3dPreset = $print3dPreset;

        return $this;
    }

    public function getTreatmentPreset(): ?TreatmentPreset
    {
        return $this->treatmentPreset;
    }

    public function setTreatmentPreset(?TreatmentPreset $treatmentPreset): static
    {
        $this->treatmentPreset = $treatmentPreset;

        return $this;
    }

    public function getFinishPreset(): ?FinishPreset
    {
        return $this->finishPreset;
    }

    public function setFinishPreset(?FinishPreset $finishPreset): static
    {
        $this->finishPreset = $finishPreset;

        return $this;
    }
}
