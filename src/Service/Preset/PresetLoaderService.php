<?php

namespace App\Service\Preset;

use App\Entity\Process\FinishProcess;
use App\Entity\Process\TreatmentProcess;
use App\Repository\RepositoryProvider;
use InvalidArgumentException;

readonly class PresetLoaderService
{
    public function __construct(
        private RepositoryProvider $repositories
    ) {}

    public function load(string $type, int $id): array
    {
        return match ($type) {
            'print3d' => $this->loadPrint3DPreset($id),
            'treatment' => $this->loadTreatmentPreset($id),
            'finish' => $this->loadFinishPreset($id),
            'global' => $this->loadGlobalPreset($id),
            default => throw new InvalidArgumentException('Type de preset invalide')
        };
    }

    private function loadPrint3DPreset(int $id): array
    {
        $preset = $this->repositories->getPrint3DPresetRepository()->find($id);
        if (!$preset) {
            throw new InvalidArgumentException('Preset introuvable');
        }

        return [
            'print3dProcess' => $preset->getPrint3dProcess()?->getId(),
            'print3dMaterial' => $preset->getPrint3dMaterial()?->getId(),
            'slicerProfil' => $preset->getSlicerProfil()?->getId()
        ];
    }

    private function loadTreatmentPreset(int $id): array
    {
        $preset = $this->repositories->getTreatmentPresetRepository()->find($id);
        if (!$preset) {
            throw new InvalidArgumentException('Preset introuvable');
        }

        return [
            'processes' => $preset->getTreatmentProcesses()->map(
                fn(TreatmentProcess $process) => [
                    'value' => $process->getId(),
                    'text' => $process->getName()
                ]
            )->toArray()
        ];
    }

    private function loadFinishPreset(int $id): array
    {
        $preset = $this->repositories->getFinishPresetRepository()->find($id);
        if (!$preset) {
            throw new InvalidArgumentException('Preset introuvable');
        }

        return [
            'processes' => $preset->getFinishProcesses()->map(
                fn(FinishProcess $process) => [
                    'value' => $process->getId(),
                    'text' => $process->getName()
                ]
            )->toArray()
        ];
    }

    private function loadGlobalPreset(int $id): array
    {
        $preset = $this->repositories->getGlobalPresetRepository()->find($id);
        if (!$preset) {
            throw new InvalidArgumentException('Preset introuvable');
        }

        return [
            'print3dPreset' => $preset->getPrint3dPreset()?->getId(),
            'treatmentPreset' => $preset->getTreatmentPreset()?->getId(),
            'finishPreset' => $preset->getFinishPreset()?->getId()
        ];
    }
}
