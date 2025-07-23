<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectForm;
use App\Repository\ProjectRepository;
use App\Service\CustomerDataFolderScannerService;
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
        SluggerInterface $slugger,
        Filesystem $filesystem
    ): Response {
        $project = new Project();

        $form = $this->createForm(ProjectForm::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($project);
            $em->flush(); // Nécessaire pour obtenir l'ID auto-généré @todo verifier les initialisation pour eviter les erreurs sql

            // Construction du nom de dossier projet
            $id = $project->getId();
            $slug = $slugger->slug($project->getTitle())->lower();
            $projectFolderName = $id . '_' . $slug;
            $projectBasePath = $this->getParameter('project_data_path') . '/' . $projectFolderName;

            // Création des dossiers pour les models et donnée clients
            $filesystem->mkdir([
                $projectBasePath,
                $projectBasePath . '/Model',
                $projectBasePath . '/CustomerData',
            ]);

            // Affectation des chemins relatifs
            $project->setModelLink($projectBasePath. '/Model');
            $project->setCustomerDataLink($projectBasePath. '/CustomerData');

            // Upload des fichiers avec renommage dynamique
            $quoteFile = $form->get('quoteLink')->getData();
            if ($quoteFile) {
                $newFilename = uniqid('quote_') . '.' . $quoteFile->guessExtension();
                $quoteFile->move($projectBasePath, $newFilename);
                $project->setQuoteLink($projectBasePath. '/' . $newFilename);
            }

            $specFile = $form->get('specificationLink')->getData();
            if ($specFile) {
                $newFilename = uniqid('spec_') . '.' . $specFile->guessExtension();
                $specFile->move($projectBasePath, $newFilename);
                $project->setSpecificationLink($projectBasePath. '/' . $newFilename);
            }

            $em->flush(); // Mise à jour avec les chemins corrects

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
    public function edit(Project $project, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ProjectForm::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($project);
            $em->flush();
            return $this->render('project/index.html.twig', []);
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
