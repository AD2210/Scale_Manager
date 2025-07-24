<?php

namespace App\Service\Preset;

use App\Entity\Preset\FinishPreset;
use App\Entity\Preset\GlobalPreset;
use App\Entity\Preset\Print3DPreset;
use App\Entity\Preset\TreatmentPreset;
use App\Repository\RepositoryProvider;
use InvalidArgumentException;

readonly class PresetCreatorService
{
    public function __construct(
        private RepositoryProvider $repositories
    ) {}

    public function create(string $type, array $data): object
    {
        return match ($type) {
            'print3d' => $this->createPrint3DPreset($data),
            'treatment' => $this->createTreatmentPreset($data),
            'finish' => $this->createFinishPreset($data),
            'global' => $this->createGlobalPreset($data),
            default => throw new InvalidArgumentException('Type de preset invalide')
        };
    }

    private function createPrint3DPreset(array $data): Print3DPreset
    {
        $preset = new Print3DPreset();
        $preset->setName($data['name']);
        $preset->setIsActive(true);

        if (!empty($data['process'])) {
            $preset->setPrint3dProcess($this->repositories->getPrint3DProcessRepository()->find($data['process']));
        }
        if (!empty($data['material'])) {
            $preset->setPrint3dMaterial($this->repositories->getPrint3DMaterialRepository()->find($data['material']));
        }
        if (!empty($data['profil'])) {
            $preset->setSlicerProfil($this->repositories->getSlicerProfilRepository()->find($data['profil']));
        }

        return $preset;
    }

    private function createTreatmentPreset(array $data): TreatmentPreset
    {
        $preset = new TreatmentPreset();
        $preset->setName($data['name']);
        $preset->setIsActive(true);

        foreach ($data['processes'] ?? [] as $processId) {
            $process = $this->repositories->getTreatmentProcessRepository()->find($processId);
            if ($process) {
                $preset->addTreatmentProcess($process);
            }
        }

        return $preset;
    }

    private function createFinishPreset(array $data): FinishPreset
    {
        $preset = new FinishPreset();
        $preset->setName($data['name']);
        $preset->setIsActive(true);

        foreach ($data['processes'] ?? [] as $processId) {
            $process = $this->repositories->getFinishProcessRepository()->find($processId);
            if ($process) {
                $preset->addFinishProcess($process);
            }
        }

        return $preset;
    }

    private function createGlobalPreset(array $data): GlobalPreset
    {
        if (empty($data['name'])) {
            throw new InvalidArgumentException('Le nom est requis');
        }

        $preset = new GlobalPreset();
        $preset->setName($data['name']);
        $preset->setIsActive(true);

        if (!empty($data['print3dPresetId'])) {
            $preset->setPrint3dPreset($this->repositories->getPrint3DPresetRepository()->find($data['print3dPresetId']));
        }
        if (!empty($data['treatmentPresetId'])) {
            $preset->setTreatmentPreset($this->repositories->getTreatmentPresetRepository()->find($data['treatmentPresetId']));
        }
        if (!empty($data['finishPresetId'])) {
            $preset->setFinishPreset($this->repositories->getFinishPresetRepository()->find($data['finishPresetId']));
        }

        return $preset;
    }
}
