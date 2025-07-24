<?php

namespace App\Service\Preset;

use App\Entity\Model;
use App\Entity\Operation\FinishOperation;
use App\Entity\Operation\TreatmentOperation;
use App\Entity\Preset\FinishPreset;
use App\Entity\Preset\GlobalPreset;
use App\Entity\Preset\Print3DPreset;
use App\Entity\Preset\TreatmentPreset;
use App\Repository\RepositoryProvider;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

readonly class PresetUpdaterService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RepositoryProvider     $repositories
    )
    {
    }

    public function update(string $type, int $id, $data): object
    {
        return match ($type) {
            'print3d' => $this->updatePrint3DPreset($id, $data),
            'treatment' => $this->updateTreatmentPreset($id, $data),
            'finish' => $this->updateFinishPreset($id, $data),
            'global' => $this->updateGlobalPreset($id, $data),
            default => throw new InvalidArgumentException('Type de preset invalide')
        };
    }

    private function updatePrint3DPreset(int $id, array $data): Print3DPreset
    {
        $preset = $this->repositories->getPrint3DPresetRepository()->find($id);

        if (!$preset) {
            throw new InvalidArgumentException('Le preset n\'existe pas');
        }

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

    private function updateTreatmentPreset(int $id, array $data): TreatmentPreset
    {
        $preset = $this->repositories->getTreatmentPresetRepository()->find($id);

        if (!$preset) {
            throw new InvalidArgumentException('Le preset n\'existe pas');
        }

        foreach ($data['processes'] ?? [] as $processId) {
            $process = $this->repositories->getTreatmentProcessRepository()->find($processId);
            if ($process) {
                $preset->addTreatmentProcess($process);
            }
        }

        return $preset;
    }

    private function updateFinishPreset(int $id, array $data): FinishPreset
    {
        $preset = $this->repositories->getFinishPresetRepository()->find($id);

        if (!$preset) {
            throw new InvalidArgumentException('Le preset n\'existe pas');
        }

        foreach ($data['processes'] ?? [] as $processId) {
            $process = $this->repositories->getFinishProcessRepository()->find($processId);
            if ($process) {
                $preset->addFinishProcess($process);
            }
        }

        return $preset;
    }

    private function updateGlobalPreset(int $id, array $data): GlobalPreset
    {
        $preset = $this->repositories->getGlobalPresetRepository()->find($id);

        if (!$preset) {
            throw new InvalidArgumentException('Le preset n\'existe pas');
        }

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

    public function updateModel(Model $model, string $field, $value): void
    {
        match ($field) {
            'print3dProcess' => $this->updateModelPrint3dProcess($model, $value),
            'print3dMaterial' => $this->updateModelPrint3dMaterial($model, $value),
            'slicerProfil' => $model->setSlicerProfil(
                $value ? $this->repositories->getSlicerProfilRepository()->find($value) : null
            ),
            'quantity' => $model->setQuantity($value),
            'isNeedSupport' => $model->setIsNeedSupport($value),
            'isNeedTest' => $model->setIsNeedTest($value),
            'treatmentOperations' => $this->updateModelTreatmentOperations($model, (array)$value),
            'finishOperations' => $this->updateModelFinishOperations($model, (array)$value),
            'print3dPreset', //voir comment sont chargé les presets dans le json
            'treatmentPreset',
            'finishPreset',
            'globalPreset' => $this->updateModelPresets($model, $field, $value),
            default => throw new InvalidArgumentException('Champ invalide')
        };
    }

    private function updateModelPresets(Model $model, string $field, $value): void
    {
        $usedPresets = $model->getUsedPresets() ?? [];
        $usedPresets[$field] = $value;
        $model->setUsedPresets($usedPresets);
    }

    private function updateModelTreatmentOperations(Model $model, array $processIds): void
    {
        // Suppression des opérations existantes
        foreach ($model->getTreatmentOperation() as $operation) {
            $this->entityManager->remove($operation);
        }
        $model->getTreatmentOperation()->clear();

        // Ajout des nouvelles opérations
        foreach ($processIds as $processId) {
            $process = $this->repositories->getTreatmentProcessRepository()->find($processId);
            if ($process) {
                $operation = new TreatmentOperation();
                $operation->setTreatmentProcess($process);
                $operation->setModel($model);
                $this->entityManager->persist($operation);
            }
        }
    }

    private function updateModelFinishOperations(Model $model, array $processIds): void
    {
        // Suppression des opérations existantes
        foreach ($model->getFinishOperation() as $operation) {
            $this->entityManager->remove($operation);
        }
        $model->getFinishOperation()->clear();

        // Ajout des nouvelles opérations
        foreach ($processIds as $processId) {
            $process = $this->repositories->getFinishProcessRepository()->find($processId);
            if ($process) {
                $operation = new FinishOperation();
                $operation->setFinishProcess($process);
                $operation->setModel($model);
                $this->entityManager->persist($operation);
            }
        }
    }

    public function updateModelPrint3dProcess($model, $value) :void
    {
        $print3dProcess = $value ? $this->repositories->getPrint3DProcessRepository()->find($value) : null;
        // On set le process ou null si vide ou null
        $model->setPrint3dProcess($print3dProcess);
        //On set les process de traitement ou finition défini par défaut dans le process d'impression
        $this->updateModelTreatmentOperations($model, $print3dProcess->getTreatmentProcess()->toArray());
        $this->updateModelFinishOperations($model, $print3dProcess->getFinishProcess()->toArray());
    }

    public function updateModelPrint3dMaterial($model, $value) :void
    {
        $print3dMaterial = $value ? $this->repositories->getPrint3DMaterialRepository()->find($value) : null;
        // On set le process ou null si vide ou null
        $model->setPrint3dMaterial($print3dMaterial);
        //On set les process de traitement ou finition défini par défaut dans le materiel d'impression
        $this->updateModelTreatmentOperations($model, $print3dMaterial->getTreatmentProcess()->toArray());
        $this->updateModelFinishOperations($model, $print3dMaterial->getFinishProcess()->toArray());
    }
}
