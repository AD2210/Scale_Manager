<?php

namespace App\Twig;

use App\Repository\ProjectRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProjectExtension extends AbstractExtension
{
    public function __construct(private ProjectRepository $projectRepository) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('active_projects', [$this, 'getActiveProjects']),
        ];
    }

    public function getActiveProjects(): array
    {
        return $this->projectRepository->findBy(['isArchived' => false], ['id' => 'ASC']);
    }
}

