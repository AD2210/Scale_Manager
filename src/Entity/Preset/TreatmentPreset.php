<?php

namespace App\Entity\Preset;

use App\Repository\Preset\TreatmentPresetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TreatmentPresetRepository::class)]
class TreatmentPreset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $name = null;

    /**
     * @var Collection<int, GlobalPreset>
     */
    #[ORM\OneToMany(targetEntity: GlobalPreset::class, mappedBy: 'treatmentPreset')]
    private Collection $globalPresets;

    #[ORM\Column]
    private ?bool $isActive = null;

    public function __construct()
    {
        $this->globalPresets = new ArrayCollection();
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

    /**
     * @return Collection<int, GlobalPreset>
     */
    public function getGlobalPresets(): Collection
    {
        return $this->globalPresets;
    }

    public function addGlobalPreset(GlobalPreset $globalPreset): static
    {
        if (!$this->globalPresets->contains($globalPreset)) {
            $this->globalPresets->add($globalPreset);
            $globalPreset->setTreatmentPreset($this);
        }

        return $this;
    }

    public function removeGlobalPreset(GlobalPreset $globalPreset): static
    {
        if ($this->globalPresets->removeElement($globalPreset)) {
            // set the owning side to null (unless already changed)
            if ($globalPreset->getTreatmentPreset() === $this) {
                $globalPreset->setTreatmentPreset(null);
            }
        }

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
}
