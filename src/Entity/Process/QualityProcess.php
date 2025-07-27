<?php

namespace App\Entity\Process;

use App\Entity\Operation\QualityOperation;
use App\Entity\Project;
use App\Repository\Process\QualityProcessRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QualityProcessRepository::class)]
class QualityProcess
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $methodLink = null;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'qualityProcess')]
    private Collection $projects;

    /**
     * @var Collection<int, QualityOperation>
     */
    #[ORM\OneToMany(targetEntity: QualityOperation::class, mappedBy: 'qualityProcess')]
    private Collection $qualityOperations;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->qualityOperations = new ArrayCollection();
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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getMethodLink(): ?string
    {
        return $this->methodLink;
    }

    public function setMethodLink(?string $methodLink): static
    {
        $this->methodLink = $methodLink;

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->addQualityProcess($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            $project->removeQualityProcess($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, QualityOperation>
     */
    public function getQualityOperations(): Collection
    {
        return $this->qualityOperations;
    }

    public function addQualityOperation(QualityOperation $qualityOperation): static
    {
        if (!$this->qualityOperations->contains($qualityOperation)) {
            $this->qualityOperations->add($qualityOperation);
            $qualityOperation->setQualityProcess($this);
        }

        return $this;
    }

    public function removeQualityOperation(QualityOperation $qualityOperation): static
    {
        if ($this->qualityOperations->removeElement($qualityOperation)) {
            // set the owning side to null (unless already changed)
            if ($qualityOperation->getQualityProcess() === $this) {
                $qualityOperation->setQualityProcess(null);
            }
        }

        return $this;
    }
}
