<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectForm;
use App\Repository\ProjectRepository;
use App\Service\CustomerDataFolderScannerService;
use App\Service\FileManagerService;
use App\Service\ModelFolderScannerService;
use App\Service\StatusCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ProjectController extends AbstractController
{
    #[Route('/', name: 'app_project_index')]
    public function index(ProjectRepository $repository): Response
    {
        $projects = $repository->findAll(); //@todo mettre critère is archived ici ou via JS dans le template pour affichage dynamique
        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/project/new', name: 'app_project_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        FileManagerService $fileManager,
    ): Response {
        $project = new Project();
        $form = $this->createForm(ProjectForm::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($project);
            $em->flush();

            // Initialisation des dossiers projet
            $fileManager->initializeProjectFolders($project);

            // Gestion des fichiers uploadés
            if ($quoteFile = $form->get('quoteLink')->getData()) {
                $project->setQuoteLink(
                    $fileManager->handleProjectFileUpload($project, $quoteFile, 'quote')
                );
            }

            if ($specFile = $form->get('specificationLink')->getData()) {
                $project->setSpecificationLink(
                    $fileManager->handleProjectFileUpload($project, $specFile, 'spec')
                );
            }

            $em->flush();
            return $this->redirectToRoute('app_project_index');
        }

        return $this->render('project/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/project/{id}', name: 'app_project_show')]
    public function show(
        Project $project,
        CustomerDataFolderScannerService $customerDataFolderScannerService,
        ModelFolderScannerService$modelFolderScannerService,
        StatusCalculator $statusCalculator,
    ): Response
    {
        // On scan les dossiers projet pour Maj en bdd
        $customerDataFolderScannerService->scan($project, false);
        $modelFolderScannerService->scan($project, false);

        // On actualise l'avancée du projet
        $modelDataset = $statusCalculator->calculateModelProgress($project);
        $customerDataDataset = $statusCalculator->calculateCustomerDataProgress($project);
        $print3dDataset = $statusCalculator->calculatePrint3dProgress($project);
        $postTreatmentDataset = $statusCalculator->calculateTreatmentProgress($project);
        $finishDataSet = $statusCalculator->calculateFinishProgress($project);
        $assemblyDataset = $statusCalculator->calculateAssemblyProgress($project);
        $qualityDataset = $statusCalculator->calculateQualityProgress($project);

        //dd($modelDataset,$customerDataDataset, $print3dDataset, $postTreatmentDataset, $finishDataSet, $assemblyDataset, $qualityDataset);

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'modelDataset' => $modelDataset,
            'customerDataDataset' => $customerDataDataset,
            'print3DDataset' => $print3dDataset,
            'postTreatmentDataset' => $postTreatmentDataset,
            'finishDataSet' => $finishDataSet,
            'assemblyDataset' => $assemblyDataset,
            'qualityDataset' => $qualityDataset,
        ]);
    }

    #[Route('/project/{id}/edit', name: 'app_project_edit')]
    public function edit(
        Project $project,
        Request $request,
        EntityManagerInterface $em,
        FileManagerService $fileManager
    ): Response {
        $form = $this->createForm(ProjectForm::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du fichier devis
            if ($quoteFile = $form->get('quoteLink')->getData()) {
                $fileManager->deleteFile($project->getQuoteLink());
                $project->setQuoteLink(
                    $fileManager->handleProjectFileUpload($project, $quoteFile, 'quote')
                );
            }

            // Gestion du fichier spécifications
            if ($specFile = $form->get('specificationLink')->getData()) {
                $fileManager->deleteFile($project->getSpecificationLink());
                $project->setSpecificationLink(
                    $fileManager->handleProjectFileUpload($project, $specFile, 'spec')
                );
            }

            $em->flush();
            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('project/edit.html.twig', [
            'form' => $form,
            'project' => $project,
        ]);
    }

    #[Route(path: '/project/{id}/archive', name: 'app_project_archive')]
    public function archive(Project $project, EntityManagerInterface $em): Response
    {
        $project->setIsArchived(!$project->isArchived());
        $em->flush();

        return $this->redirectToRoute('app_project_show',['id' => $project->getId()]);
    }

    #[Route('/project/{id}/file/{type}', name: 'project_file_download')]
    public function download(Project $project, string $type): Response
    {
        $filePath = match ($type) {
            'quote' => $project->getQuoteLink(),
            'specification' => $project->getSpecificationLink(),
            default => throw $this->createNotFoundException('Type de fichier invalide'),
        };

        if (!$filePath || !file_exists($filePath)) {
            throw $this->createNotFoundException('Fichier introuvable');
        }

        /** @noinspection UseControllerShortcuts */
        return (new BinaryFileResponse($filePath))
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
