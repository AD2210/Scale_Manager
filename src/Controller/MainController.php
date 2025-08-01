<?php

namespace App\Controller;

use App\Entity\Project;
use App\Service\StatusCalculator;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class MainController extends AbstractController
{
    //route de test hors bdd
    #[Route('/main', name: 'app_main')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig', []);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(ProjectRepository $projectRepository, StatusCalculator $status): Response
    {
        $slots = [];
        for ($i = 1; $i <= 4; $i++) {
            $project = $projectRepository->findOneBy(['dashboardSlot' => $i]);
            $slots[$i] = $project ? $status->getDashboardDataset($project) : null;
        }
        $projects = $projectRepository->findBy(['isArchived' => false], ['id' => 'DESC']);
        return $this->render('main/dashboard.html.twig', [
            'projects' => $projects,
            'projectSlots' => $slots, // données complète de chaque projet en fct des slots choisis
        ]);
    }

    #[Route('/dashboard/assign/{slot}/{id}', name: 'app_dashboard_assign', methods: ['POST'])]
    public function assignDashboardSlot(int $slot, Project $project, ProjectRepository $repo, EntityManagerInterface $em): Response
    {
        // Reset tous les projets déjà sur ce slot
        foreach ($repo->findBy(['dashboardSlot' => $slot]) as $p) {
            $p->setDashboardSlot(null);
        }

        $project->setDashboardSlot($slot);
        $em->flush();

        return new Response(status: 204); // No Content
    }
}
