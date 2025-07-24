<?php

namespace App\Entity;

use App\Entity\Base\SlicerProfil;
use App\Entity\Base\SubContractor;
use App\Entity\Enum\Print3DStatusEnum;
use App\Entity\Operation\FinishOperation;
use App\Entity\Operation\TreatmentOperation;
use App\Entity\Process\AssemblyProcess;
use App\Entity\Process\Print3DMaterial;
use App\Entity\Process\Print3DProcess;
use App\Entity\Process\QualityProcess;
use App\Repository\ModelRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModelRepository::class)]
class Model
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $fileName = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?bool $isReadyToPrint = false;

    #[ORM\Column]
    private ?bool $isNeedTest = false;

    #[ORM\Column]
    private ?bool $isNeedSupport = false;

    #[ORM\ManyToOne]
    private ?SlicerProfil $slicerProfil = null;

    #[ORM\Column]
    private ?bool $isSubContracted = false;

    #[ORM\ManyToOne(inversedBy: 'models')]
    private ?SubContractor $subcontractor = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTime $deliverDate = null;

    #[ORM\Column]
    private ?bool $isDelivered = false;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $usedPresets = [
        'global' => null,      // ID du preset global ou null si modifié
        'print3d' => null,     // ID du preset d'impression ou null si modifié
        'treatment' => null,   // ID du preset de traitement ou null si modifié
        'finish' => null       // ID du preset de finition ou null si modifié
    ];

    #[ORM\ManyToOne]
    private ?Print3DProcess $print3dProcess = null;

    #[ORM\ManyToOne]
    private ?Print3DMaterial $print3dMaterial = null;

    /**
     * @var Collection<int, TreatmentOperation>
     */
    #[ORM\OneToMany(targetEntity: TreatmentOperation::class, mappedBy: 'model')]
    private Collection $treatmentOperation;

    /**
     * @var Collection<int, FinishOperation>
     */
    #[ORM\OneToMany(targetEntity: FinishOperation::class, mappedBy: 'model')]
    private Collection $finishOperation;

    /**
     * @var Collection<int, AssemblyProcess>
     */
    #[ORM\ManyToMany(targetEntity: AssemblyProcess::class)]
    private Collection $assemblyProcess;

    #[ORM\Column]
    private ?bool $isAssemblyDone = false;

    /**
     * @var Collection<int, QualityProcess>
     */
    #[ORM\ManyToMany(targetEntity: QualityProcess::class)]
    private Collection $qualityProcess;

    #[ORM\Column]
    private ?bool $isQualityOk = false;

    #[ORM\Column(enumType: Print3DStatusEnum::class)]
    private ?Print3DStatusEnum $print3dStatus = Print3DStatusEnum::TODO;

    #[ORM\ManyToOne]
    private ?Project $project = null;

    public function __construct()
    {
        $this->treatmentOperation = new ArrayCollection();
        $this->finishOperation = new ArrayCollection();
        $this->assemblyProcess = new ArrayCollection();
        $this->qualityProcess = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function isReadyToPrint(): ?bool
    {
        return $this->isReadyToPrint;
    }

    public function setIsReadyToPrint(bool $isReadyToPrint): static
    {
        $this->isReadyToPrint = $isReadyToPrint;

        return $this;
    }

    public function isNeedTest(): ?bool
    {
        return $this->isNeedTest;
    }

    public function setIsNeedTest(bool $isNeedTest): static
    {
        $this->isNeedTest = $isNeedTest;

        return $this;
    }

    public function isNeedSupport(): ?bool
    {
        return $this->isNeedSupport;
    }

    public function setIsNeedSupport(bool $isNeedSupport): static
    {
        $this->isNeedSupport = $isNeedSupport;

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

    public function isSubContracted(): ?bool
    {
        return $this->isSubContracted;
    }

    public function setIsSubContracted(bool $isSubContracted): static
    {
        $this->isSubContracted = $isSubContracted;

        return $this;
    }

    public function getSubcontractor(): ?SubContractor
    {
        return $this->subcontractor;
    }

    public function setSubcontractor(?SubContractor $subcontractor): static
    {
        $this->subcontractor = $subcontractor;

        return $this;
    }

    public function getDeliverDate(): ?DateTime
    {
        return $this->deliverDate;
    }

    public function setDeliverDate(?DateTime $deliverDate): static
    {
        $this->deliverDate = $deliverDate;

        return $this;
    }

    public function isDelivered(): ?bool
    {
        return $this->isDelivered;
    }

    public function setIsDelivered(bool $isDelivered): static
    {
        $this->isDelivered = $isDelivered;

        return $this;
    }

    public function getUsedPresets(): ?array
    {
        return $this->usedPresets;
    }

    public function setUsedPresets(?array $usedPresets): static
    {
        $this->usedPresets = $usedPresets;
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

    /**
     * @return Collection<int, TreatmentOperation>
     */
    public function getTreatmentOperation(): Collection
    {
        return $this->treatmentOperation;
    }

    public function addTreatmentOperation(TreatmentOperation $treatmentOperation): static
    {
        if (!$this->treatmentOperation->contains($treatmentOperation)) {
            $this->treatmentOperation->add($treatmentOperation);
            $treatmentOperation->setModel($this);
        }

        return $this;
    }

    public function removeTreatmentOperation(TreatmentOperation $treatmentOperation): static
    {
        if ($this->treatmentOperation->removeElement($treatmentOperation)) {
            // set the owning side to null (unless already changed)
            if ($treatmentOperation->getModel() === $this) {
                $treatmentOperation->setModel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, FinishOperation>
     */
    public function getFinishOperation(): Collection
    {
        return $this->finishOperation;
    }

    public function addFinishOperation(FinishOperation $finishOperation): static
    {
        if (!$this->finishOperation->contains($finishOperation)) {
            $this->finishOperation->add($finishOperation);
            $finishOperation->setModel($this);
        }

        return $this;
    }

    public function removeFinishOperation(FinishOperation $finishOperation): static
    {
        if ($this->finishOperation->removeElement($finishOperation)) {
            // set the owning side to null (unless already changed)
            if ($finishOperation->getModel() === $this) {
                $finishOperation->setModel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AssemblyProcess>
     */
    public function getAssemblyProcess(): Collection
    {
        return $this->assemblyProcess;
    }

    public function addAssemblyProcess(AssemblyProcess $assemblyProcess): static
    {
        if (!$this->assemblyProcess->contains($assemblyProcess)) {
            $this->assemblyProcess->add($assemblyProcess);
        }

        return $this;
    }

    public function removeAssemblyProcess(AssemblyProcess $assemblyProcess): static
    {
        $this->assemblyProcess->removeElement($assemblyProcess);

        return $this;
    }

    public function isAssemblyDone(): ?bool
    {
        return $this->isAssemblyDone;
    }

    public function setIsAssemblyDone(bool $isAssemblyDone): static
    {
        $this->isAssemblyDone = $isAssemblyDone;

        return $this;
    }

    /**
     * @return Collection<int, QualityProcess>
     */
    public function getQualityProcess(): Collection
    {
        return $this->qualityProcess;
    }

    public function addQualityProcess(QualityProcess $qualityProcess): static
    {
        if (!$this->qualityProcess->contains($qualityProcess)) {
            $this->qualityProcess->add($qualityProcess);
        }

        return $this;
    }

    public function removeQualityProcess(QualityProcess $qualityProcess): static
    {
        $this->qualityProcess->removeElement($qualityProcess);

        return $this;
    }

    public function isQualityOk(): ?bool
    {
        return $this->isQualityOk;
    }

    public function setIsQualityOk(bool $isQualityOk): static
    {
        $this->isQualityOk = $isQualityOk;

        return $this;
    }

    public function getPrint3dStatus(): ?Print3DStatusEnum
    {
        return $this->print3dStatus;
    }

    public function setPrint3dStatus(Print3DStatusEnum $print3dStatus): static
    {
        $this->print3dStatus = $print3dStatus;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function updateUsedPresets(string $type, ?int $presetId, ?int $globalPresetId = null): void
    {
        if (!$this->usedPresets) {
            $this->usedPresets = [];
        }

        if ($presetId) {
            $this->usedPresets[$type] = [
                'preset_id' => $presetId,
                'global_preset_id' => $globalPresetId
            ];
        } else {
            unset($this->usedPresets[$type]);
        }
    }

}
