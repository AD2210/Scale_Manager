<?php

namespace App\Entity\Preset;

use App\Entity\Process\FinishProcess;
use App\Repository\Preset\FinishPresetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FinishPresetRepository::class)]
class FinishPreset
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
    #[ORM\OneToMany(targetEntity: GlobalPreset::class, mappedBy: 'finishPreset')]
    private Collection $globalPresets;

    #[ORM\Column]
    private ?bool $isActive = null;

    /**
     * @var Collection<int, FinishProcess>
     */
    #[ORM\ManyToMany(targetEntity: FinishProcess::class, inversedBy: 'finishPresets')]
    private Collection $finishProcesses;

    public function __construct()
    {
        $this->globalPresets = new ArrayCollection();
        $this->finishProcesses = new ArrayCollection();
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
            $globalPreset->setFinishPreset($this);
        }

        return $this;
    }

    public function removeGlobalPreset(GlobalPreset $globalPreset): static
    {
        if ($this->globalPresets->removeElement($globalPreset)) {
            // set the owning side to null (unless already changed)
            if ($globalPreset->getFinishPreset() === $this) {
                $globalPreset->setFinishPreset(null);
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

    /**
     * @return Collection<int, FinishProcess>
     */
    public function getFinishProcesses(): Collection
    {
        return $this->finishProcesses;
    }

    public function addFinishProcess(FinishProcess $finishProcess): static
    {
        if (!$this->finishProcesses->contains($finishProcess)) {
            $this->finishProcesses->add($finishProcess);
        }

        return $this;
    }

    public function removeFinishProcess(FinishProcess $finishProcess): static
    {
        $this->finishProcesses->removeElement($finishProcess);

        return $this;
    }
}
