<?php

namespace App\Service\Preset;

use App\Repository\Preset\FinishPresetRepository;
use App\Repository\Preset\GlobalPresetRepository;
use App\Repository\Preset\Print3DPresetRepository;
use App\Repository\Preset\TreatmentPresetRepository;
use InvalidArgumentException;

readonly class PresetRepositoryService
{
    public function __construct(
        private Print3DPresetRepository   $print3DPresetRepository,
        private TreatmentPresetRepository $treatmentPresetRepository,
        private FinishPresetRepository    $finishPresetRepository,
        private GlobalPresetRepository    $globalPresetRepository
    ) {}

    public function getPresetById(string $type, int $id): ?object
    {
        return match ($type) {
            'print3d' => $this->print3DPresetRepository->find($id),
            'treatment' => $this->treatmentPresetRepository->find($id),
            'finish' => $this->finishPresetRepository->find($id),
            'global' => $this->globalPresetRepository->find($id),
            default => throw new InvalidArgumentException('Type de preset invalide')
        };
    }
}
