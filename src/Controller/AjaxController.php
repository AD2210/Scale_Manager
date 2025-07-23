<?php

namespace App\Controller;

use App\Entity\Project;
use App\Service\CustomerDataFolderScannerService;
use App\Service\ModelFolderScannerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AjaxController extends AbstractController
{
    #[Route('/api/project/{id}/archive', name: 'app_api_project_archive')]
    public function archive(Project $project, EntityManagerInterface $em): JsonResponse
    {
        $project->setIsArchived($project->isArchived());
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'isArchived' => $project->isArchived(),
        ]);
    }

    #[Route('/api/project/{id}/deadline', name: 'app_api_project_deadline', methods: ['POST'])]
    public function updateDeadline(
        Request $request,
        Project $project,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['error' => 'Requête invalide'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);
        $date = $data['deadline'] ?? null;

        if (!$date) {
            return new JsonResponse(['error' => 'Date manquante'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $project->setDeadline(new \DateTime($date));
            $em->flush();

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la mise à jour'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/project/{id}/customer-data-link', name: 'app_api_project_customer_data_link', methods: ['POST'])]
    public function updateCustomerDataLink(
        Request $request,
        Project $project,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['error' => 'Requête invalide'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);
        $link = $data['link'] ?? null;

        if (!$link) {
            return new JsonResponse(['error' => 'Lien manquant'], Response::HTTP_BAD_REQUEST);
        }

        $project->setCustomerDataLink($link);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }


    #[Route('/api/project/{id}/model-link', name: 'app_api_project_model_link', methods: ['POST'])]
    public function updateModelLink(
        Request $request,
        Project $project,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['error' => 'Requête invalide'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);
        $link = $data['link'] ?? null;

        if (!$link) {
            return new JsonResponse(['error' => 'Lien manquant'], Response::HTTP_BAD_REQUEST);
        }

        $project->setModelLink($link);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/api/project/{id}/sync-folders', name: 'app_api_project_sync_folders', methods: ['POST'])]
    public function resyncFolders(
        Project $project,
        Request $request,
        CustomerDataFolderScannerService $customerScanner,
        ModelFolderScannerService $modelScanner,
    ): JsonResponse {
        $deleteOrphans = json_decode($request->getContent(), true)['deleteOrphans'] ?? false;

        $customerStats = $customerScanner->scan($project, $deleteOrphans);
        $modelStats = $modelScanner->scan($project, $deleteOrphans);

        return $this->json([
            'message' => 'Dossiers synchronisés.',
            'customerData' => $customerStats,
            'model' => $modelStats,
        ]);
    }

    #[Route('/api/project/{id}/upload/{type}', name: 'app_api_project_file_upload', methods: ['POST'])]
    public function uploadFile(Project $project, string $type, Request $request, ParameterBagInterface $params, EntityManagerInterface $em): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file) {
            return new JsonResponse(['error' => 'Aucun fichier'], 400);
        }

        $path = $params->get('project_root_dir');
        $filename = uniqid() . '.' . $file->guessExtension();
        $file->move($path, $filename);

        match ($type) {
            'quote' => $project->setQuoteLink($path . '/' . $filename),
            'specification' => $project->setSpecificationLink($path . '/' . $filename),
            default => throw new \InvalidArgumentException('Type invalide'),
        };

        $em->flush();
        return new JsonResponse(['success' => true]);
    }

    #[Route('/api/project/{id}/delete/{type}', name: 'app_api_project_file_delete', methods: ['POST'])]
    public function deleteFile(Project $project, string $type, ParameterBagInterface $params, EntityManagerInterface $em): JsonResponse
    {
        $filePath = match ($type) {
            'quote' => $project->getQuoteLink(),
            'specification' => $project->getSpecificationLink(),
            default => throw new \InvalidArgumentException('Type invalide'),
        };

        if ($filePath && file_exists($filePath)) {
            unlink($filePath);
        }

        match ($type) {
            'quote' => $project->setQuoteLink(null),
            'specification' => $project->setSpecificationLink(null),
        };

        $em->flush();
        return new JsonResponse(['success' => true]);
    }

}
