<?php

namespace App\Service\Project;

use App\Entity\Model;
use App\Entity\Project;
use App\Repository\ModelRepository;

readonly class ProjectModelService
{
    public function __construct(
        private ModelRepository $modelRepository
    ) {}

    public function getCurrentModel(Project $project, int $index): ?Model
    {
        $projectModels = $this->modelRepository->findBy(['project' => $project]);
        $totalFiles = count($projectModels);

        if ($index < 0 || $index >= $totalFiles) {
            $index = 0;
        }

        return $projectModels[$index] ?? null;
    }

    public function createPagination(Project $project, int $currentIndex): array
    {
        $totalFiles = count($this->modelRepository->findBy(['project' => $project]));

        return [
            'prev' => $currentIndex > 0 ? $currentIndex - 1 : null,
            'next' => $currentIndex < ($totalFiles - 1) ? $currentIndex + 1 : null,
            'current' => $currentIndex,
            'total' => $totalFiles
        ];
    }
}
