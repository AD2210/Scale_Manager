<?php

namespace App\Service;

use App\Entity\Project;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileManagerService
{
    public function __construct(
        private readonly ParameterBagInterface $params,
        private readonly SluggerInterface $slugger,
        private readonly Filesystem $filesystem,
    ) {
    }

    private function getProjectBasePath(Project $project): string
    {
        $id = $project->getId();
        $slug = $this->slugger->slug($project->getTitle())->lower();
        $projectFolderName = $id . '_' . $slug;
        return $this->params->get('project_data_path') . '/' . $projectFolderName;
    }

    public function initializeProjectFolders(Project $project): void
    {
        $projectBasePath = $this->getProjectBasePath($project);

        // Création des dossiers pour les models et données clients
        $this->filesystem->mkdir([
            $projectBasePath,
            $projectBasePath . '/Model',
            $projectBasePath . '/CustomerData',
        ]);

        // Affectation des chemins relatifs
        $project->setModelLink($projectBasePath . '/Model');
        $project->setCustomerDataLink($projectBasePath . '/CustomerData');
    }

    public function handleProjectFileUpload(Project $project, UploadedFile $file, string $type): string
    {
        $projectBasePath = $this->getProjectBasePath($project);
        $newFilename = uniqid($type . '_') . '.' . $file->guessExtension();
        $file->move($projectBasePath, $newFilename);

        return $projectBasePath . '/' . $newFilename;
    }

    public function deleteFile(?string $filePath): void
    {
        if ($filePath && file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
