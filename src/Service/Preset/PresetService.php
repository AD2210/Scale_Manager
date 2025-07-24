<?php

namespace App\Service\Preset;

use App\Entity\Preset\GlobalPreset;
use App\Repository\ModelRepository;
use App\Repository\Preset\FinishPresetRepository;
use App\Repository\Preset\GlobalPresetRepository;
use App\Repository\Preset\Print3DPresetRepository;
use App\Repository\Preset\TreatmentPresetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

readonly class PresetService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PresetUpdaterService   $presetUpdater,
        private PresetLoaderService    $presetLoader,
        private PresetCreatorService   $presetCreator,
        private ModelRepository        $modelRepository,
    )
    {
    }

    public function updatePreset(string $type, int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $preset =$this->presetUpdater->update($type, $id, $data);
            $this->entityManager->persist($preset);
            $this->entityManager->flush();

            return new JsonResponse(['success' => true]);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function loadPreset(string $type, int $id): JsonResponse
    {
        try {
            $data = $this->presetLoader->load($type, $id);
            return new JsonResponse($data);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateModel(int $fileId, Request $request): JsonResponse
    {
        $model = $this->modelRepository->find($fileId);
        if (!$model) {
            return new JsonResponse(['error' => 'Modèle non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        try {
            $this->presetUpdater->updateModel($model, $data['field'] ?? '', $data['value'] ?? null);
            $this->entityManager->flush();

            return new JsonResponse(['success' => true]);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function savePreset(string $type, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $preset = $this->presetCreator->create($type, $data);

            $this->entityManager->persist($preset);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'preset' => [
                    'id' => $preset->getId(),
                    'name' => $preset->getName()
                ]
            ]);
        } catch (Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
