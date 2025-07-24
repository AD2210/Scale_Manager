<?php

namespace App\Service\Gamme;

use App\Entity\Model;
use App\Entity\Operation\FinishOperation;
use App\Entity\Operation\TreatmentOperation;
use App\Entity\Preset\GlobalPreset;
use App\Entity\Project;
use App\Form\FinishProcessAutocompleteType;
use App\Form\TreatmentProcessAutocompleteType;
use App\Repository\RepositoryProvider;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

readonly class GammeViewService
{
    public function __construct(
        private RepositoryProvider   $repositories,
        private FormFactoryInterface $formFactory
    ) {}

    public function getPresetViewData(?GlobalPreset $globalPreset): array
    {
        $globalPreset ??= new GlobalPreset(); // permet de s'assurer qu'un objet existe pour acceder aux méthodes
        return [
            'treatmentProcessForm' => $this->createTreatmentProcessForm($globalPreset),
            'finishProcessForm' => $this->createFinishProcessForm($globalPreset),
            'globalPreset' => $globalPreset?->getId(),
            'print3dPreset' => $globalPreset->getPrint3dPreset()?->getId(),
            'treatmentPreset' => $globalPreset->getTreatmentPreset()?->getId(),
            'finishPreset' => $globalPreset->getFinishPreset()?->getId(),
            'print3dProcess'=> $globalPreset->getPrint3dPreset()?->getPrint3dProcess(),
            'print3dMaterial'=> $globalPreset->getPrint3dPreset()?->getPrint3dMaterial(),
            'slicerProfil'=> $globalPreset->getPrint3dPreset()?->getSlicerProfil(),
            'treatmentOperations' => $globalPreset->getTreatmentPreset()?->getTreatmentProcesses() ?? [],// Ici en réalité ce ne sont pas des opérations, car pas lié au modèle, mais juste une liste de process lié au preset.
            'finishOperations' => $globalPreset->getFinishPreset()?->getFinishProcesses() ?? [],// idem ici
            ...$this->getCommonViewData() // ... opérateur de dispersion renvoie le contenu éclaté (clé → valeur)
        ];
    }

    public function getProjectFileViewData(Project $project, Model $model, array $pagination): array
    {
        //dd($model);
        // on récupère les presets stocké dans le json
        $globalPreset = (int)$model->getUsedPresets()['global'] ?? null;
        $print3dPreset = (int)$model->getUsedPresets()['print3d'] ?? null;
        $treatmentPreset = (int)$model->getUsedPresets()['treatment'] ?? null;
        $finishPreset = (int)$model->getUsedPresets()['finish'] ?? null;

        //dd($model->getTreatmentOperation(), $model->getFinishOperation());
        return [
            'project' => $project,
            'model' => $model,
            'pagination' => $pagination,
            'treatmentProcessForm' => $this->createTreatmentProcessForm($model),
            'finishProcessForm' => $this->createFinishProcessForm($model),
            'globalPreset' => $globalPreset,
            'print3dPreset' => $print3dPreset,
            'treatmentPreset' => $treatmentPreset,
            'finishPreset' => $finishPreset,
            'print3dProcess'=> $model->getPrint3dProcess(),
            'print3dMaterial'=> $model->getPrint3dMaterial(),
            'slicerProfil'=> $model->getSlicerProfil(),
            'treatmentOperations'=> $model->getTreatmentOperation(),
            'finishOperations'=> $model->getFinishOperation(),
            ...$this->getCommonViewData()
        ];
    }

    private function getCommonViewData(): array
    {
        return [
            'globalPresets' => $this->repositories->getGlobalPresetRepository()->findAll(),
            'print3dPresets' => $this->repositories->getPrint3DPresetRepository()->findAll(),
            'treatmentPresets' => $this->repositories->getTreatmentPresetRepository()->findAll(),
            'finishPresets' => $this->repositories->getFinishPresetRepository()->findAll(),
            'print3dProcesses' => $this->repositories->getPrint3DProcessRepository()->findAll(),
            'print3dMaterials' => $this->repositories->getPrint3DMaterialRepository()->findAll(),
            'treatmentProcesses' => $this->repositories->getTreatmentProcessRepository()->findAll(),
            'finishProcesses' => $this->repositories->getFinishProcessRepository()->findAll(),
            'slicerProfils' => $this->repositories->getSlicerProfilRepository()->findAll(),
            // ... autres données communes
        ];
    }

    private function createTreatmentProcessForm($entity): FormInterface
    {
        if ($entity instanceof Model) {
            return $this->formFactory->create(TreatmentProcessAutocompleteType::class, null, [
                'data' => $entity->getTreatmentOperation()->map(function(TreatmentOperation $operation) {
                    return $operation->getTreatmentProcess();
                })->toArray()
            ]);
        }
        return $this->formFactory->create(TreatmentProcessAutocompleteType::class, null, [
            'data' => $entity->getTreatmentPreset()?->getTreatmentProcesses()?->toArray() ?? []
        ]);
    }

    private function createFinishProcessForm($entity): FormInterface
    {
        if ($entity instanceof Model) {
            return $this->formFactory->create(FinishProcessAutocompleteType::class, null, [
                'data' => $entity->getFinishOperation()->map(function(FinishOperation $operation) {
                    return $operation->getFinishProcess();
                })->toArray()
            ]);
        }
        return $this->formFactory->create(FinishProcessAutocompleteType::class, null, [
            'data' => $entity->getFinishPreset()?->getFinishProcesses()?->toArray() ?? []
        ]);
    }
}
