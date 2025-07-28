<?php

namespace App\Controller;

use App\Entity\Enum\Print3DStatusEnum;
use App\Entity\Project;
use App\Repository\Base\CustomerRepository;
use App\Repository\Base\ManagerRepository;
use App\Repository\Base\SoftwareRepository;
use App\Repository\Base\SubContractorRepository;
use App\Repository\Base\SlicerProfilRepository;
use App\Repository\ModelRepository;
use App\Repository\Operation\AssemblyOperationRepository;
use App\Repository\Operation\FinishOperationRepository;
use App\Repository\Operation\QualityOperationRepository;
use App\Repository\Operation\TreatmentOperationRepository;
use App\Repository\Process\Print3DMaterialRepository;
use App\Repository\Process\Print3DProcessRepository;
use App\Repository\Process\TreatmentProcessRepository;
use App\Repository\Process\FinishProcessRepository;
use App\Repository\Process\AssemblyProcessRepository;
use App\Repository\Process\QualityProcessRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ListController extends AbstractController
{
    #[Route('/customers', name: 'app_base_customer_list')]
    public function customerList(CustomerRepository $repo): Response
    {
        return $this->render('base/customer/list.html.twig', [
            'items' => $repo->findAll(),
        ]);
    }

    #[Route('/managers', name: 'app_base_manager_list')]
    public function managerList(ManagerRepository $repo): Response
    {
        return $this->render('base/manager/list.html.twig', [
            'items' => $repo->findAll(),
        ]);
    }

    #[Route('/softwares', name: 'app_base_software_list')]
    public function softwareList(SoftwareRepository $repo): Response
    {
        return $this->render('base/software/list.html.twig', [
            'items' => $repo->findAll(),
        ]);
    }

    #[Route('/sub-contractors', name: 'app_base_sub_contractor_list')]
    public function subContractorList(SubContractorRepository $repo): Response
    {
        return $this->render('base/subContractor/list.html.twig', [
            'items' => $repo->findAll(),
        ]);
    }

    #[Route('/slicer-profils', name: 'app_base_slicer_profil_list')]
    public function slicerProfilList(SlicerProfilRepository $repo): Response
    {
        return $this->render('base/slicerProfil/list.html.twig', [
            'items' => $repo->findAll(),
        ]);
    }

    #[Route('/print3d-material', name: 'app_process_print3d_material_list')]
    public function print3dMaterialList(Print3DMaterialRepository $repo, TreatmentProcessRepository $treatRepo, FinishProcessRepository $finishRepo): Response
    {
        return $this->render('process/print3dMaterial/list.html.twig', [
            'items' => $repo->findAll(),
            'treatments' => $treatRepo->findBy(['isActive' => true]),
            'finishes' => $finishRepo->findBy(['isActive' => true]),
        ]);
    }

    #[Route('/print3d-process', name: 'app_process_print3d_process_list')]
    public function print3dProcessList(Print3DProcessRepository $repo, TreatmentProcessRepository $treatRepo, FinishProcessRepository $finishRepo): Response
    {
        return $this->render('process/print3dProcess/list.html.twig', [
            'items' => $repo->findAll(),
            'treatments' => $treatRepo->findBy(['isActive' => true]),
            'finishes' => $finishRepo->findBy(['isActive' => true]),
        ]);
    }

    #[Route('/treatment-process', name: 'app_process_treatment_process_list')]
    public function treatmentProcessList(TreatmentProcessRepository $repo): Response
    {
        return $this->render('process/treatmentProcess/list.html.twig', [
            'items' => $repo->findAll(),
        ]);
    }

    #[Route('/finish-process', name: 'app_process_finish_process_list')]
    public function finishProcessList(FinishProcessRepository $repo): Response
    {
        return $this->render('process/finishProcess/list.html.twig', [
            'items' => $repo->findAll(),
        ]);
    }

    #[Route('/assembly-process', name: 'app_process_assembly_process_list')]
    public function assemblyProcessList(AssemblyProcessRepository $repo): Response
    {
        return $this->render('process/assemblyProcess/list.html.twig', [
            'items' => $repo->findAll(),
        ]);
    }

    #[Route('/quality-process', name: 'app_process_quality_process_list')]
    public function qualityProcessList(QualityProcessRepository $repo): Response
    {
        return $this->render('process/qualityProcess/list.html.twig', [
            'items' => $repo->findAll(),
        ]);
    }

    #[Route('/model/project{id}', name: 'app_model_list')]
    public function modelList(Project $project, ModelRepository $repo, SlicerProfilRepository $profilRepository): Response
    {
        return $this->render('model/list.html.twig', [
            'items' => $repo->findBy(['project' => $project]),
            'project' => $project,
            'slicerProfils' => $profilRepository->findBy(['isActive' => true]),
        ]);
    }

    #[Route('/print3d/project{id}', name: 'app_print3d_list')]
    public function print3dList(
        Project                   $project,
        ModelRepository           $modelRepository,
        Print3DProcessRepository  $processRepository,
        Print3DMaterialRepository $materialRepository
    ): Response
    {
        return $this->render('print3d/list.html.twig', [
            'items' => $modelRepository->findBy(['project' => $project]),
            'project' => $project,
            'print3dProcesses' => $processRepository->findBy(['isActive' => true]),
            'print3dMaterials' => $materialRepository->findBy(['isActive' => true]),
            'print3dStatuses' => Print3DStatusEnum::cases(),
        ]);
    }

    #[Route('/treatment/project{id}', name: 'app_treatment_list')]
    public function treatmentList(
        Project                      $project,
        ModelRepository              $modelRepository,
        TreatmentProcessRepository   $processRepository,
        TreatmentOperationRepository $operationRepository,
    ): Response
    {
        $models = $modelRepository->findBy(['project' => $project]);
        return $this->render('treatment/list.html.twig', [
            'items' => $models,
            'project' => $project,
            'treatmentProcesses' => $processRepository->findBy(['isActive' => true]),
            'treatmentOperations' => $operationRepository->findBy(['model' => $models]),
        ]);
    }

    #[Route('/finish/project{id}', name: 'app_finish_list')]
    public function finishList(
        Project                   $project,
        ModelRepository           $modelRepository,
        FinishProcessRepository   $processRepository,
        FinishOperationRepository $operationRepository,
    ): Response
    {
        $models = $modelRepository->findBy(['project' => $project]);
        return $this->render('finish/list.html.twig', [
            'items' => $models,
            'project' => $project,
            'finishProcesses' => $processRepository->findBy(['isActive' => true]),
            'finishOperations' => $operationRepository->findBy(['model' => $models]),
        ]);
    }

    #[Route('/assembly/project{id}', name: 'app_assembly_list')]
    public function assemblyList(
        Project                   $project,
        ModelRepository           $modelRepository,
        AssemblyProcessRepository   $processRepository,
        AssemblyOperationRepository $operationRepository,
    ): Response
    {
        $models = $modelRepository->findBy(['project' => $project]);
        return $this->render('assembly/list.html.twig', [
            'items' => $models,
            'project' => $project,
            'assemblyProcesses' => $processRepository->findBy(['isActive' => true]),
            'assemblyOperations' => $operationRepository->findBy(['model' => $models]),
        ]);
    }

    #[Route('/quality/project{id}', name: 'app_quality_list')]
    public function qualityList(
        Project                   $project,
        ModelRepository           $modelRepository,
        QualityProcessRepository   $processRepository,
        QualityOperationRepository $operationRepository,
    ): Response
    {
        $models = $modelRepository->findBy(['project' => $project]);
        return $this->render('quality/list.html.twig', [
            'items' => $models,
            'project' => $project,
            'qualityProcesses' => $processRepository->findBy(['isActive' => true]),
            'qualityOperations' => $operationRepository->findBy(['model' => $models]),
        ]);
    }
}
