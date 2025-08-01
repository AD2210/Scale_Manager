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
            $customerDataList = $this->customerDataRepository->findBy(['project' => $project]);

            if (empty($customerDataList)) {
                return ['error' => 'Aucune donnée dans le dossier.'];
            }

            $done = 0;
            $total = count($customerDataList);

            foreach ($customerDataList as $cd) {
                $mainDone = $cd->getCustomerDataOperations()
                    ->filter(fn($op) =>
                        $op->isIsDone() &&
                        $op->getSoftware()?->isActive() &&
                        $op->getSoftware()?->isMain()
                    )
                    ->count();

                if ($mainDone > 0) {
                    $done++;
                }
            }

            if ($total === 0) {
                return ['error' => 'Aucune donnée client détectée.'];
            }

            $progress = round(($done / $total) * 100, 2);

            return compact('done', 'total', 'progress');
        } catch (\Throwable $e) {
            error_log('Erreur dans calculateCustomerDataProgress: ' . $e->getMessage());
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

    public function computeGlobalProgress(Project $project): float
    {
        $customerDataProgress = $this->calculateCustomerDataProgress($project);
        $modelProgress = $this->calculateModelProgress($project);
        $print3DProgress = $this->calculatePrint3DProgress($project);
        $assemblyProgress = $this->calculateAssemblyProgress($project);
        $qualityProgress = $this->calculateQualityProgress($project);
        $treatmentProgress = $this->calculateTreatmentProgress($project);
        $finishProgress = $this->calculateFinishProgress($project);

        $totalProgress = array_sum([
            $customerDataProgress['progress'] ?? 0,
            $modelProgress['progress'] ?? 0,
            $print3DProgress['progress'] ?? 0,
            $assemblyProgress['progress'] ?? 0,
            $qualityProgress['progress'] ?? 0,
            $treatmentProgress['progress'] ?? 0,
            $finishProgress['progress'] ?? 0
        ]);

        return round(($totalProgress / 7) , 0);
    }

    public function getDashboardDataset(Project $project): array
{
    return [
        'title' => $project->getTitle(),
        'customer' => $project->getCustomer()?->getName() ?? 'Client inconnu',
        'manager' => $project->getManager()?->getName() ?? 'Non défini',
        'deadline' => $project->getDeadline(),
        'globalProgress' => $this->computeGlobalProgress($project),

        'workflowSteps' => [
            [
                'label' => 'Données client',
                'percent' => round($this->calculateCustomerDataProgress($project)['progress'] ?? 0, 0)
            ],
            [
                'label' => 'Modèles 3D',
                'percent' => round($this->calculateModelProgress($project)['progress'] ?? 0,0)
            ],
            [
                'label' => 'Impression 3D',
                'percent' => round($this->calculatePrint3DProgress($project)['progress'] ?? 0,0)
            ],
            [
                'label' => 'Post-traitement',
                'percent' => round($this->calculateTreatmentProgress($project)['progress'] ?? 0,0)
            ],
            [
                'label' => 'Finition',
                'percent' => round($this->calculateFinishProgress($project)['progress'] ?? 0,0)
            ],
            [
                'label' => 'Assemblage',
                'percent' => round($this->calculateAssemblyProgress($project)['progress'] ?? 0,0)
            ],
            [
                'label' => 'Qualité',
                'percent' => round($this->calculateQualityProgress($project)['progress'] ?? 0,0)
            ],
        ],
    ];
}
}

