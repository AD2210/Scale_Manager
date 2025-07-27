<?php

namespace App\Entity\Preset;

use App\Entity\Base\SlicerProfil;
use App\Entity\Process\Print3DMaterial;
use App\Entity\Process\Print3DProcess;
use App\Repository\Preset\Print3DPresetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: Print3DPresetRepository::class)]
class Print3DPreset
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
    #[ORM\OneToMany(targetEntity: GlobalPreset::class, mappedBy: 'print3dPreset')]
    private Collection $globalPresets;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\ManyToOne(inversedBy: 'print3DPresets')]
    private ?Print3DProcess $print3dProcess = null;

    #[ORM\ManyToOne(inversedBy: 'print3DPresets')]
    private ?Print3DMaterial $print3dMaterial = null;

    #[ORM\ManyToOne(inversedBy: 'print3DPresets')]
    private ?SlicerProfil $slicerProfil = null;

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
            $globalPreset->setPrint3dPreset($this);
        }

        return $this;
    }

    public function removeGlobalPreset(GlobalPreset $globalPreset): static
    {
        if ($this->globalPresets->removeElement($globalPreset)) {
            // set the owning side to null (unless already changed)
            if ($globalPreset->getPrint3dPreset() === $this) {
                $globalPreset->setPrint3dPreset(null);
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

    public function getPrint3dProcess(): ?Print3DProcess
    {
        return $this->print3dProcess;
    }

    public function setPrint3dProcess(?Print3DProcess $print3dProcess): static
    {
        $this->print3dProcess = $print3dProcess;

        return $this;
    }

    public function getPrint3dMaterial(): ?Print3DMaterial
    {
        return $this->print3dMaterial;
    }

    public function setPrint3dMaterial(?Print3DMaterial $print3dMaterial): static
    {
        $this->print3dMaterial = $print3dMaterial;

        return $this;
    }

    public function getSlicerProfil(): ?SlicerProfil
    {
        return $this->slicerProfil;
    }

    public function setSlicerProfil(?SlicerProfil $slicerProfil): static
    {
        $this->slicerProfil = $slicerProfil;

        return $this;
    }
}
