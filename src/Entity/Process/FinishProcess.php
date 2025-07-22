<?php

namespace App\Entity\Process;

use App\Entity\Preset\FinishPreset;
use App\Repository\Process\FinishProcessRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: FinishProcessRepository::class)]
class FinishProcess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Groups(['autocomplete'])]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    /**
     * @var Collection<int, FinishPreset>
     */
    #[ORM\ManyToMany(targetEntity: FinishPreset::class, mappedBy: 'finishProcesses')]
    private Collection $finishPresets;

    public function __construct()
    {
        $this->finishPresets = new ArrayCollection();
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
     * @return Collection<int, FinishPreset>
     */
    public function getFinishPresets(): Collection
    {
        return $this->finishPresets;
    }

    public function addFinishPreset(FinishPreset $finishPreset): static
    {
        if (!$this->finishPresets->contains($finishPreset)) {
            $this->finishPresets->add($finishPreset);
            $finishPreset->addFinishProcess($this);
        }

        return $this;
    }

    public function removeFinishPreset(FinishPreset $finishPreset): static
    {
        if ($this->finishPresets->removeElement($finishPreset)) {
            $finishPreset->removeFinishProcess($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
