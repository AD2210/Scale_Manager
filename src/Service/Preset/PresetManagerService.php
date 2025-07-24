<?php

namespace App\Service\Preset;

use App\Entity\Model;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

readonly class PresetManagerService
{
    public function __construct(
        private EntityManagerInterface   $entityManager,
        private PresetRepositoryService  $presetRepository,
        private PresetComparisonService  $presetComparison,
        private PresetApplicationService $presetApplication
    ) {}

    public function handlePresetApplication(Model $model, string $type, int $presetId, ?int $globalPresetId = null): void
    {
        dd('handle');
        $preset = $this->presetRepository->getPresetById($type, $presetId);
        $currentValues = $this->getCurrentValues($model, $type);

        $this->presetApplication->applyPresetValues($model, $currentValues, $type);

        $usedPresets = $model->getUsedPresets() ?? [];

        if ($this->presetComparison->areValuesEqualToPreset($currentValues, $preset, $type)) {
            $usedPresets[$type] = $presetId;
            if ($type !== 'global') {
                $usedPresets['global'] = $globalPresetId;
            }
        } else {
            $usedPresets[$type] = null;
            if ($type !== 'global') {
                $usedPresets['global'] = null;
            }
        }

        $model->setUsedPresets($usedPresets);
        $this->entityManager->flush();
    }

    private function getCurrentValues(Model $model, string $type): array
    {
        return match ($type) {
            'print3d' => [
                'print3dProcess' => $model->getPrint3dProcess()?->getId(),
                'print3dMaterial' => $model->getPrint3dMaterial()?->getId(),
                'slicerProfil' => $model->getSlicerProfil()?->getId(),
            ],
            'treatment' => [
                'processes' => $model->getTreatmentOperation()
                    ->map(fn($op) => $op->getTreatmentProcess()->getId())
                    ->toArray()
            ],
            'finish' => [
                'processes' => $model->getFinishOperation()
                    ->map(fn($op) => $op->getFinishProcess()->getId())
                    ->toArray()
            ],
            default => throw new InvalidArgumentException('Type de preset invalide')
        };
    }
}
