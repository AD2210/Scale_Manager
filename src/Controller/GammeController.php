<?php

namespace App\Controller;

use App\Entity\Model;
use App\Entity\Preset\FinishPreset;
use App\Entity\Preset\GlobalPreset;
use App\Entity\Preset\Print3DPreset;
use App\Entity\Preset\TreatmentPreset;
use App\Entity\Process\FinishProcess;
use App\Entity\Process\TreatmentProcess;
use App\Entity\Project;
use App\Form\FinishProcessAutocompleteType;
use App\Form\TreatmentProcessAutocompleteType;
use App\Repository\Base\SlicerProfilRepository;
use App\Repository\ModelRepository;
use App\Repository\Preset\FinishPresetRepository;
use App\Repository\Preset\GlobalPresetRepository;
use App\Repository\Preset\Print3DPresetRepository;
use App\Repository\Preset\TreatmentPresetRepository;
use App\Repository\Process\FinishProcessRepository;
use App\Repository\Process\Print3DMaterialRepository;
use App\Repository\Process\Print3DProcessRepository;
use App\Repository\Process\TreatmentProcessRepository;
use App\Service\EntityManagerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/gamme')]
class GammeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GlobalPresetRepository $globalPresetRepository,
        private readonly Print3DPresetRepository $print3DPresetRepository,
        private readonly TreatmentPresetRepository $treatmentPresetRepository,
        private readonly FinishPresetRepository $finishPresetRepository,
        private readonly Print3DProcessRepository $print3DProcessRepository,
        private readonly Print3DMaterialRepository $print3DMaterialRepository,
        private readonly SlicerProfilRepository $slicerProfilRepository,
        private readonly ModelRepository $modelRepository,
        private readonly TreatmentProcessRepository $treatmentProcessRepository,
        private readonly FinishProcessRepository $finishProcessRepository,
    ) {
    }

    #[Route(name: 'app_gamme_preset', methods: ['GET'])]
    public function preset(GlobalPreset $globalPreset): Response
    {
        $treatmentProcessForm = $this->createForm(TreatmentProcessAutocompleteType::class, null, [
            'attr' => ['data-controller' => 'symfony--ux-autocomplete--autocomplete']
        ]);
        $finishProcessForm = $this->createForm(FinishProcessAutocompleteType::class, null)->createView();

        return $this->render('gamme/index.html.twig', [
            'treatmentProcessForm' => $treatmentProcessForm,
            'finishProcessForm' => $finishProcessForm,
            'globalPreset' => $globalPreset,
            'globalPresets' => $this->globalPresetRepository->findAll(),
            'print3dPresets' => $this->print3DPresetRepository->findAll(),
            'treatmentPresets' => $this->treatmentPresetRepository->findAll(),
            'finishPresets' => $this->finishPresetRepository->findAll(),
            'print3dProcesses' => $this->print3DProcessRepository->findAll(),
            'print3dMaterials' => $this->print3DMaterialRepository->findAll(),
            'treatmentProcesses' => $this->treatmentProcessRepository->findAll(),
            'finishProcesses' => $this->finishProcessRepository->findAll(),
            'slicerProfils' => $this->slicerProfilRepository->findAll(),
            'print3dPreset' => $globalPreset->getPrint3dPreset(),
            'treatmentPreset' => $globalPreset->getTreatmentPreset(),
            'finishPreset' => $globalPreset->getFinishPreset(),
            'print3dProcess' => $globalPreset->getPrint3dPreset()?->getPrint3dProcess(),
            'print3dMaterial' => $globalPreset->getPrint3dPreset()?->getPrint3dMaterial(),
            'slicerProfil' => $globalPreset->getPrint3dPreset()?->getSlicerProfil(),
            'treatmentOperations' => $globalPreset->getTreatmentPreset()?->getTreatmentProcesses() ?? [],
            'finishOperations' => $globalPreset->getFinishPreset()?->getFinishProcesses() ?? [],
        ]);
    }

    #[Route('/project/{projectId}/file/{fileId}', name: 'app_gamme_project_file', methods: ['GET'])]
    public function projectFile(
        #[MapEntity (id: 'projectId')] Project $project,
        int $fileId
    ): Response
    {
        $model = $this->entityManager->getReference(Model::class, $fileId);

        // Récupération de l'index du modèle dans le projet
        $projectModels = $this->modelRepository->findBy(['project' => $project]);
        $currentIndex = array_search($model, $projectModels);

        return $this->render('gamme/index.html.twig', [
            'project' => $project,
            'model' => $model,
            'currentIndex' => $currentIndex,
            'totalFiles' => count($projectModels),
            'globalPresets' => $this->globalPresetRepository->findAll(),
            'print3dPresets' => $this->print3DPresetRepository->findAll(),
            'treatmentPresets' => $this->treatmentPresetRepository->findAll(),
            'finishPresets' => $this->finishPresetRepository->findAll(),
            'print3dProcesses' => $this->print3DProcessRepository->findAll(),
            'print3dMaterials' => $this->print3DMaterialRepository->findAll(),
            'treatmentProcesses' => $this->treatmentProcessRepository->findAll(),
            'finishProcesses' => $this->finishProcessRepository->findAll(),
            'slicerProfils' => $this->slicerProfilRepository->findAll(),
            'print3dPreset' => $model->getPrint3dPreset(),
            'treatmentPreset' => $model->getTreatmentPreset(),
            'finishPreset' => $model->getFinishPreset(),
            'globalPreset' => $model->getGlobalPreset(),
            'print3dProcess' => $model->getPrint3dProcess(),
            'print3dMaterial' => $model->getPrint3dMaterial(),
            'slicerProfil' => $model->getSlicerProfil(),
            'treatmentOperations' => $model->getTreatmentOperation(),
            'finishOperations' => $model->getFinishOperation(),
            // Les liens de navigation seront ajoutés plus tard
        ]);
    }

    #[Route('/api/preset/{id}/update', name: 'app_gamme_update_preset', methods: ['POST'])]
    public function updatePreset(
        GlobalPreset $globalPreset,
        Request $request,
        EntityManagerService $entityManagerService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $field = $data['field'] ?? null;
        $value = $data['value'] ?? null;

        if (!$field) {
            return $this->json(['error' => 'Champ manquant'], Response::HTTP_BAD_REQUEST);
        }

        try {
            match ($field) {
                'print3dPreset' => $globalPreset->setPrint3dPreset(
                    $value ? $this->print3DPresetRepository->find($value) : null
                ),
                'treatmentPreset' => $entityManagerService->updateManyToManyRelations(
                    $globalPreset,
                    'treatmentProcesses',
                    $value,
                    ['entity' => TreatmentProcess::class, 'method' => 'addTreatmentProcess']
                ),
                'finishPreset' => $entityManagerService->updateManyToManyRelations(
                    $globalPreset,
                    'finishProcesses',
                    $value,
                    ['entity' => FinishProcess::class, 'method' => 'addFinishProcess']
                ),
                'print3dProcess' => $globalPreset->getPrint3dPreset()?->setPrint3dProcess(
                    $value ? $this->print3DProcessRepository->find($value) : null
                ),
                'print3dMaterial' => $globalPreset->getPrint3dPreset()?->setPrint3dMaterial(
                    $value ? $this->print3DMaterialRepository->find($value) : null
                ),
                'slicerProfil' => $globalPreset->getPrint3dPreset()?->setSlicerProfil(
                    $value ? $this->slicerProfilRepository->find($value) : null
                ),
                default => throw new \InvalidArgumentException('Champ invalide')
            };

            $this->entityManager->flush();
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }


    #[Route('/api/project/{projectId}/file/{fileId}/update', name: 'app_gamme_update_model', methods: ['POST'])]
    public function updateModel(
        #[MapEntity(id: 'projectId')] Project $project,
        int $fileId,
        Request $request
    ): JsonResponse {
        $model = $this->modelRepository->find($fileId);
        if (!$model) {
            return $this->json(['error' => 'Modèle non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $field = $data['field'] ?? null;
        $value = $data['value'] ?? null;

        if (!$field) {
            return $this->json(['error' => 'Champ manquant'], Response::HTTP_BAD_REQUEST);
        }

        try {
            match ($field) {
                'globalPreset' => $model->setGlobalPreset(
                    $value ? $this->globalPresetRepository->find($value) : null
                ),
                'print3dProcess' => $model->setPrint3dProcess(
                    $value ? $this->print3DProcessRepository->find($value) : null
                ),
                'print3dMaterial' => $model->setPrint3dMaterial(
                    $value ? $this->print3DMaterialRepository->find($value) : null
                ),
                'slicerProfil' => $model->setSlicerProfil(
                    $value ? $this->slicerProfilRepository->find($value) : null
                ),
                'assemblyName' => $model->setAssemblyName($value),
                'assemblyComment' => $model->setAssemblyComment($value),
                'isAssemblySpecifique' => $model->setIsAssemblySpecifique((bool) $value),
                default => throw new \InvalidArgumentException('Champ invalide')
            };

            $this->entityManager->flush();
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/preset/save-print3d', name: 'app_gamme_save_print3d_preset', methods: ['POST'])]
    public function savePrint3dPreset(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;
        $process = $data['process'] ?? null;
        $material = $data['material'] ?? null;
        $profil = $data['profil'] ?? null;

        if (!$name) {
            return $this->json(['error' => 'Nom manquant'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $preset = new Print3DPreset();
            $preset->setName($name);

            if ($process) {
                $preset->setPrint3dProcess($this->print3DProcessRepository->find($process));
            }
            if ($material) {
                $preset->setPrint3dMaterial($this->print3DMaterialRepository->find($material));
            }
            if ($profil) {
                $preset->setSlicerProfil($this->slicerProfilRepository->find($profil));
            }

            $this->entityManager->persist($preset);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'preset' => [
                    'id' => $preset->getId(),
                    'name' => $preset->getName()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/preset/save-treatment', name: 'app_gamme_save_treatment_preset', methods: ['POST'])]
    public function saveTreatmentPreset(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;
        $processes = $data['processes'] ?? [];

        if (!$name) {
            return $this->json(['error' => 'Nom manquant'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $preset = new TreatmentPreset();
            $preset->setName($name);
            $preset->setIsActive(true);

            // Gestion des processus avec ManyToMany
            foreach ($processes as $processId) {
                $process = $this->treatmentProcessRepository->find($processId);
                if ($process) {
                    $preset->addTreatmentProcess($process);
                }
            }

            $this->entityManager->persist($preset);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'preset' => [
                    'id' => $preset->getId(),
                    'name' => $preset->getName()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/preset/save-finish', name: 'app_gamme_save_finish_preset', methods: ['POST'])]
    public function saveFinishPreset(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? null;
        $processes = $data['processes'] ?? [];

        if (!$name) {
            return $this->json(['error' => 'Nom manquant'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $preset = new FinishPreset();
            $preset->setName($name);
            $preset->setIsActive(true);

            // Gestion des processus avec ManyToMany
            foreach ($processes as $processId) {
                $process = $this->finishProcessRepository->find($processId);
                if ($process) {
                    $preset->addFinishProcess($process);
                }
            }

            $this->entityManager->persist($preset);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'preset' => [
                    'id' => $preset->getId(),
                    'name' => $preset->getName()
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
