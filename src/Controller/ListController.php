<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\Base\CustomerRepository;
use App\Repository\Base\ManagerRepository;
use App\Repository\Base\SoftwareRepository;
use App\Repository\Base\SubContractorRepository;
use App\Repository\Base\SlicerProfilRepository;
use App\Repository\ModelRepository;
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
    #[Route('/customers', name: 'app_customer_list')]
    public function customerList(CustomerRepository $repo): Response
    {
        return $this->render('base/customer/list.html.twig', [
            'items' => $repo->findAll(),
        ]);
    }

    #[Route('/managers', name: 'app_manager_list')]
    public function managerList(ManagerRepository $repo): Response
    {
        return $this->render('base/manager/list.html.twig', [
            'items' => $repo->findAll(),
        ]);
    }

    #[Route('/softwares', name: 'app_software_list')]
    public function softwareList(SoftwareRepository $repo): Response
    {
        return $this->render('base/software/list.html.twig', [
            'items' => $repo->findAll(),
        ]);
    }

    #[Route('/sub-contractors', name: 'app_sub_contractor_list')]
    public function subContractorList(SubContractorRepository $repo): Response
    {
        return $this->render('base/subContractor/list.html.twig', [
            'items' => $repo->findAll(),
        ]);
    }

    #[Route('/slicer-profils', name: 'app_slicer_profil_list')]
    public function slicerProfilList(SlicerProfilRepository $repo): Response
    {
        return $this->render('base/slicerProfil/list.html.twig', [
            'items' => $repo->findAll(),
        ]);
    }

    #[Route('/print3d-material', name: 'app_print3d_material_list')]
    public function print3dMaterialList(Print3DMaterialRepository $repo, TreatmentProcessRepository $treatRepo, FinishProcessRepository $finishRepo): Response
    {
        return $this->render('process/print3dMaterial/list.html.twig', [
            'items' => $repo->findAll(),
            'treatments' => $treatRepo->findAll(),
            'finishes' => $finishRepo->findAll(),
        ]);
    }

    #[Route('/print3d-process', name: 'app_print3d_process_list')]
    public function print3dProcessList(Print3DProcessRepository $repo, TreatmentProcessRepository $treatRepo, FinishProcessRepository $finishRepo): Response
    {
        return $this->render('process/print3dProcess/list.html.twig', [
            'items' => $repo->findAll(),
            'treatments' => $treatRepo->findAll(),
            'finishes' => $finishRepo->findAll(),
        ]);
    }

    #[Route('/treatment-process', name: 'app_treatment_process_list')]
    public function treatmentProcessList(TreatmentProcessRepository $repo): Response
    {
        return $this->render('process/treatmentProcess/list.html.twig', [
            'items' => $repo->findAll(),
        ]);
    }

    #[Route('/finish-process', name: 'app_finish_process_list')]
    public function finishProcessList(FinishProcessRepository $repo): Response
    {
        return $this->render('process/finishProcess/list.html.twig', [
            'items' => $repo->findAll(),
        ]);
    }

    #[Route('/assembly-process', name: 'app_assembly_process_list')]
    public function assemblyProcessList(AssemblyProcessRepository $repo): Response
    {
        return $this->render('process/assemblyProcess/list.html.twig', [
            'items' => $repo->findAll(),
        ]);
    }

    #[Route('/quality-process', name: 'app_quality_process_list')]
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
            'slicerProfils' => $profilRepository->findAll(),
        ]);
    }
}
