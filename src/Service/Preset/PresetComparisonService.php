<?php

Namespace App\Service\Preset;

use App\Entity\Preset\FinishPreset;
use App\Entity\Preset\Print3DPreset;
use App\Entity\Preset\TreatmentPreset;

class PresetComparisonService
{
    public function areValuesEqualToPreset($currentValues, $preset, string $type): bool
    {
        return match ($type) {
            'print3d' => $this->comparePrint3dValues($currentValues, $preset),
            'treatment' => $this->compareTreatmentValues($currentValues, $preset),
            'finish' => $this->compareFinishValues($currentValues, $preset),
            default => false
        };
    }

    private function comparePrint3dValues($values, Print3DPreset $preset): bool
    {
        return $values['print3dProcess'] === $preset->getPrint3dProcess()?->getId() &&
               $values['print3dMaterial'] === $preset->getPrint3dMaterial()?->getId() &&
               $values['slicerProfil'] === $preset->getSlicerProfil()?->getId();
    }

    private function compareTreatmentValues($values, TreatmentPreset $preset): bool
    {
        $presetProcessIds = $preset->getTreatmentProcesses()->map(fn($p) => $p->getId())->toArray();
        sort($presetProcessIds);
        sort($values['treatmentProcesses']);

        return $presetProcessIds === $values['treatmentProcesses'];
    }

    private function compareFinishValues($values, FinishPreset $preset): bool
    {
        $presetProcessIds = $preset->getFinishProcesses()->map(fn($p) => $p->getId())->toArray();
        sort($presetProcessIds);
        sort($values['finishProcesses']);

        return $presetProcessIds === $values['finishProcesses'];
    }
}
