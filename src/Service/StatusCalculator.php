<?php

namespace App\Service;

use App\Entity\Enum\Print3DStatusEnum;
use App\Entity\Project;
use App\Repository\CustomerDataRepository;
use App\Repository\ModelRepository;
use Exception;

readonly class StatusCalculator
{
    public function __construct(
        private CustomerDataRepository          $customerDataRepository,
        private ModelRepository                 $modelRepository,
    )
    {
    }

    public function calculateCustomerDataProgress(Project $project): array
    {
        try {
            $cds = $this->customerDataRepository->findBy(['project' => $project]);
            $total = 0;
            $done = 0;


            foreach ($cds as $cd) {
                $operations = $cd->getCustomerDataOperations();

                foreach ($operations as $op) {
                    $software = $op->getSoftware();
                    if ($software && $software->isActive()) {
                        $total++;
                        if ($op->isIsDone()) {
                            $done++;
                        }
                    }
                }
            }

            if (empty($cds)) {
                return ['error' => 'Aucune données dans le dossier'];
            }

            if ($total === 0) {
                return ['error' => 'Aucun logiciel actif n’est configuré pour les fichiers client.'];
            }

            $progress = round(($done / $total) * 100, 2);

            return compact('done', 'total', 'progress');
        } catch (Exception $e) {
            error_log('Erreur dans calculateCustomerDataProgress: ' . $e->getMessage());
            error_log($e->getTraceAsString());
            throw $e;
        }
    }



    public function calculateModelProgress(Project $project): array
    {
        $models = $this->modelRepository->findBy(['project' => $project]);
        if (empty($models)) return ['error' => 'Aucun modèle dans le dossier'];

        $total = count($models);
        $done = count(array_filter($models, fn($m) => $m->isReadyToPrint()));
        $progress = round(($done / $total) * 100, 2);

        return compact('done', 'total', 'progress');
    }

    public function calculatePrint3DProgress(Project $project): array
    {
        return $this->calculateFromModelQuantity($project, fn($m) => $m->getPrint3dStatus() === Print3DStatusEnum::DONE, 'Aucun modèle disponible pour l\'impression 3D.');
    }

    public function calculateAssemblyProgress(Project $project): array
    {
        return $this->calculateOperationProgress($project, 'getAssemblyOperation', 'Aucunes opération d\'assemblage trouvée');
    }

    public function calculateQualityProgress(Project $project): array
    {
        return $this->calculateOperationProgress($project, 'getQualityOperation', 'Aucunes opération qualité trouvée');
    }

    public function calculateTreatmentProgress(Project $project): array
    {
        return $this->calculateOperationProgress($project, 'getTreatmentOperation', 'Aucunes opération de post-traitement trouvée');
    }

    public function calculateFinishProgress(Project $project): array
    {
        return $this->calculateOperationProgress($project, 'getFinishOperation', 'Aucune opération de finition trouvée');
    }

    private function calculateFromModelQuantity(Project $project, callable $condition, string $emptyMsg): array
    {
        $models = $this->modelRepository->findBy(['project' => $project]);
        if (empty($models)) return ['error' => $emptyMsg];

        $total = 0;
        $done = 0;

        foreach ($models as $model) {
            $qty = $model->getQuantity() ?? 0;
            $total += $qty;
            if ($condition($model)) {
                $done += $qty;
            }
        }

        if ($total === 0) return ['error' => 'Les quantités sont nulles ou absentes'];

        $progress = round(($done / $total) * 100, 2);
        return compact('done', 'total', 'progress');
    }

    private function calculateOperationProgress(Project $project, string $getter, string $emptyMsg): array
    {
        $models = $this->modelRepository->findBy(['project' => $project]);
        $total = 0;
        $done = 0;
        $operations = 0;

        foreach ($models as $model) {
            $operationsByModel = $model->$getter();
            $count = count($operationsByModel);
            $operations += $count;
            $qty = $model->getQuantity() ?? 0;
            $total += $qty * $count;

            foreach ($operationsByModel as $operation) {
                if ($operation->isDone()) {
                    $done += $qty;
                }
            }
        }

        if ($operations === 0) return ['error' => $emptyMsg];
        if ($total === 0) return ['error' => 'Les quantités des modèles sont nulles ou absentes'];

        $progress = round(($done / $total) * 100, 2);
        return compact('done', 'total', 'progress');
    }
}

