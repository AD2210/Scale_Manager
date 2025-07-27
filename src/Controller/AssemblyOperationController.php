<?php

namespace App\Controller;

use App\Entity\Model;
use App\Entity\Operation\AssemblyOperation;
use App\Repository\Process\AssemblyProcessRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/assembly', name: 'api_assembly_')]
class AssemblyOperationController extends AbstractController
{
    #[Route('/operation/{id}', name: 'update_operation', methods: ['POST'])]
    public function updateOperation(
        Model $model,
        Request $request,
        EntityManagerInterface $em,
        AssemblyProcessRepository $processRepository
    ): JsonResponse {
        $content = json_decode($request->getContent(), true);

        if (!isset($content['field'])) {
            return $this->json(['error' => 'Paramètres manquants'], Response::HTTP_BAD_REQUEST);
        }

        // Gestion de la mise à jour du process
        if ($content['field'] === 'assemblyProcess') {
            // Cas de la suppression
            if (empty($content['value']) && !empty($content['operationId'])) {
                $operationId = $content['operationId'];
                $operation = $em->getRepository(AssemblyOperation::class)->find($operationId);

                if (!$operation) {
                    return $this->json([
                        'error' => 'Opération non trouvée',
                        'details' => [
                            'operationId' => $operationId,
                            'modelId' => $model->getId()
                        ]
                    ], Response::HTTP_NOT_FOUND);
                }

                $em->remove($operation);
                $em->flush();
                return $this->json(['success' => true]);
            }

            // Récupérer le process
            $process = $processRepository->find($content['value']);
            if (!$process) {
                return $this->json(['error' => 'Process non trouvé'], Response::HTTP_NOT_FOUND);
            }

            // Cas de la mise à jour
            if (!empty($content['operationId'])) {
                $operation = $em->getRepository(AssemblyOperation::class)->find($content['operationId']);
                if (!$operation) {
                    return $this->json(['error' => 'Opération non trouvée'], Response::HTTP_NOT_FOUND);
                }
                $operation->setFinishProcess($process);
            } else {
                // Cas de la création
                $operation = new AssemblyOperation();
                $operation->setModel($model);
                $operation->setAssemblyProcess($process);
                $operation->setIsDone(false);
                $em->persist($operation);
            }

            $em->flush();

            return $this->json([
                'success' => true,
                'operation' => [
                    'id' => $operation->getId(),
                    'isDone' => $operation->isDone()
                ]
            ]);
        }

        // Gestion de la mise à jour du isDone
        if ($content['field'] === 'isDone') {
            $operationId = $content['entityId'] ?? null;
            if (!$operationId) {
                return $this->json(['error' => 'ID opération manquant'], Response::HTTP_BAD_REQUEST);
            }

            $operation = $em->getRepository(AssemblyOperation::class)->find($operationId);
            if (!$operation) {
                return $this->json(['error' => 'Opération non trouvée'], Response::HTTP_NOT_FOUND);
            }

            $operation->setIsDone($content['value']);
            $em->flush();

            return $this->json(['success' => true]);
        }

        return $this->json(['error' => 'Champ non géré'], Response::HTTP_BAD_REQUEST);
    }
}
