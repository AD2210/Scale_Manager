<?php

namespace App\Repository;

use App\Repository\Base\SlicerProfilRepository;
use App\Repository\Preset\FinishPresetRepository;
use App\Repository\Preset\GlobalPresetRepository;
use App\Repository\Preset\Print3DPresetRepository;
use App\Repository\Preset\TreatmentPresetRepository;
use App\Repository\Process\FinishProcessRepository;
use App\Repository\Process\Print3DMaterialRepository;
use App\Repository\Process\Print3DProcessRepository;
use App\Repository\Process\TreatmentProcessRepository;

readonly class RepositoryProvider
{
    public function __construct(
        private GlobalPresetRepository     $globalPresetRepository,
        private Print3DPresetRepository    $print3DPresetRepository,
        private TreatmentPresetRepository  $treatmentPresetRepository,
        private FinishPresetRepository     $finishPresetRepository,
        private Print3DProcessRepository   $print3DProcessRepository,
        private Print3DMaterialRepository  $print3DMaterialRepository,
        private SlicerProfilRepository     $slicerProfilRepository,
        private TreatmentProcessRepository $treatmentProcessRepository,
        private FinishProcessRepository    $finishProcessRepository
    ) {}

    public function getGlobalPresetRepository(): GlobalPresetRepository
    {
        return $this->globalPresetRepository;
    }

    public function getPrint3DPresetRepository(): Print3DPresetRepository
    {
        return $this->print3DPresetRepository;
    }

    public function getTreatmentPresetRepository(): TreatmentPresetRepository
    {
        return $this->treatmentPresetRepository;
    }

    public function getFinishPresetRepository(): FinishPresetRepository
    {
        return $this->finishPresetRepository;
    }

    public function getPrint3DProcessRepository(): Print3DProcessRepository
    {
        return $this->print3DProcessRepository;
    }

    public function getPrint3DMaterialRepository(): Print3DMaterialRepository
    {
        return $this->print3DMaterialRepository;
    }

    public function getSlicerProfilRepository(): SlicerProfilRepository
    {
        return $this->slicerProfilRepository;
    }

    public function getTreatmentProcessRepository(): TreatmentProcessRepository
    {
        return $this->treatmentProcessRepository;
    }

    public function getFinishProcessRepository(): FinishProcessRepository
    {
        return $this->finishProcessRepository;
    }
}
