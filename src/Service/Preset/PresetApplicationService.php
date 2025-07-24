<?php

namespace App\Service\Preset;

use App\Entity\Model;
use App\Entity\Operation\FinishOperation;
use App\Entity\Operation\TreatmentOperation;
use App\Repository\RepositoryProvider;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

readonly class PresetApplicationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RepositoryProvider     $repositories
    ) {}

    public function applyPresetValues(Model $model, array $values, string $type): void
    {
        match ($type) {
            'print3d' => $this->applyPrint3dValues($model, $values),
            'treatment' => $this->applyTreatmentValues($model, $values),
            'finish' => $this->applyFinishValues($model, $values),
            default => throw new InvalidArgumentException('Type de preset invalide')
        };
    }

    private function applyPrint3dValues(Model $model, array $values): void
    {
        if (isset($values['print3dProcess'])) {
            $process = $this->repositories->getPrint3DProcessRepository()->find($values['print3dProcess']);
            $model->setPrint3dProcess($process);
        }

        if (isset($values['print3dMaterial'])) {
            $material = $this->repositories->getPrint3DMaterialRepository()->find($values['print3dMaterial']);
            $model->setPrint3dMaterial($material);
        }

        if (isset($values['slicerProfil'])) {
            $profil = $this->repositories->getSlicerProfilRepository()->find($values['slicerProfil']);
            $model->setSlicerProfil($profil);
        }
    }

    private function applyTreatmentValues(Model $model, array $values): void
    {
        if (!isset($values['processes'])) {
            return;
        }

        // Suppression des opérations existantes
        foreach ($model->getTreatmentOperation() as $operation) {
            $this->entityManager->remove($operation);
        }
        $model->getTreatmentOperation()->clear();

        // Ajout des nouvelles opérations
        foreach ($values['processes'] as $processId) {
            $process = $this->repositories->getTreatmentProcessRepository()->find($processId);
            if ($process) {
                $operation = new TreatmentOperation();
                $operation->setTreatmentProcess($process);
                $operation->setModel($model);
                $this->entityManager->persist($operation);
                $model->addTreatmentOperation($operation);
            }
        }
    }

    private function applyFinishValues(Model $model, array $values): void
    {
        if (!isset($values['processes'])) {
            return;
        }

        // Suppression des opérations existantes
        foreach ($model->getFinishOperation() as $operation) {
            $this->entityManager->remove($operation);
        }
        $model->getFinishOperation()->clear();

        // Ajout des nouvelles opérations
        foreach ($values['processes'] as $processId) {
            $process = $this->repositories->getFinishProcessRepository()->find($processId);
            if ($process) {
                $operation = new FinishOperation();
                $operation->setFinishProcess($process);
                $operation->setModel($model);
                $this->entityManager->persist($operation);
                $model->addFinishOperation($operation);
            }
        }
    }
}
