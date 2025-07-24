<?php

namespace App\Controller;

use App\Entity\Preset\GlobalPreset;
use App\Entity\Project;
use App\Service\Gamme\GammeViewService;
use App\Service\Preset\PresetService;
use App\Service\Project\ProjectModelService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/gamme')]
class GammeController extends AbstractController
{
    public function __construct(
        private readonly PresetService       $presetService,
        private readonly GammeViewService    $gammeViewService,
        private readonly ProjectModelService $projectModelService,
    )
    {
    }

    #[Route(name: 'app_gamme_preset', methods: ['GET'])]
    public function preset(?GlobalPreset $globalPreset = null): Response
    {
        $globalPreset ??= new GlobalPreset();

        return $this->render('gamme/index.html.twig',
            $this->gammeViewService->getPresetViewData($globalPreset)
        );
    }

    #[Route('/project/{id}/file', name: 'app_gamme_project_file', methods: ['GET'])]
    public function projectFile(Project $project, Request $request): Response
    {
        $model = $this->projectModelService->getCurrentModel($project, $request->query->getInt('index'));
        $pagination = $this->projectModelService->createPagination($project, $request->query->getInt('index'));

        return $this->render('gamme/index.html.twig', $this->gammeViewService->getProjectFileViewData($project, $model, $pagination));
    }

    #[Route('/api/preset/update/{type}/{id}', name: 'app_gamme_update_preset', methods: ['POST'])]
    public function updatePreset(string $type, int $id, Request $request): JsonResponse
    {
        return $this->presetService->updatePreset($type, $id, $request);
    }

    #[Route('/api/preset/{type}/{id}/load', name: 'app_gamme_load_preset', methods: ['GET'])]
    public function loadPreset(string $type, int $id): JsonResponse
    {
        return $this->presetService->loadPreset($type, $id);
    }

    //@todo supprimer l'id projet qui est inutile ici (l'id du file garantie l'unicité de la route)
    #[Route('/api/project/{projectId}/file/{fileId}/update', name: 'app_gamme_update_model', methods: ['POST'])]
    public function updateModel(int $projectId, int $fileId, Request $request): JsonResponse
    {
        return $this->presetService->updateModel($fileId, $request);
    }

    #[Route('/api/preset/save/{type}', name: 'app_preset_save', methods: ['POST'])]
    public function savePreset(string $type, Request $request): JsonResponse
    {
        return $this->presetService->savePreset($type, $request);
    }
}
