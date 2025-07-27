<?php

namespace App\Entity\Base;

use App\Entity\Preset\Print3DPreset;
use App\Repository\Base\SlicerProfilRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SlicerProfilRepository::class)]
class SlicerProfil
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fileLink = null;

    /**
     * @var Collection<int, Print3DPreset>
     */
    #[ORM\OneToMany(targetEntity: Print3DPreset::class, mappedBy: 'slicerProfil')]
    private Collection $print3DPresets;

    public function __construct()
    {
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

    public function getFileLink(): ?string
    {
        return $this->fileLink;
    }

    public function setFileLink(?string $fileLink): static
    {
        $this->fileLink = $fileLink;

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
            $print3DPreset->setSlicerProfil($this);
        }

        return $this;
    }

    public function removePrint3DPreset(Print3DPreset $print3DPreset): static
    {
        if ($this->print3DPresets->removeElement($print3DPreset)) {
            // set the owning side to null (unless already changed)
            if ($print3DPreset->getSlicerProfil() === $this) {
                $print3DPreset->setSlicerProfil(null);
            }
        }

        return $this;
    }
}
