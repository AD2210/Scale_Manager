<?php

namespace App\Service;

use App\Entity\Project;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileManagerService
{
    private const UPLOAD_FOLDERS = [
        'slicer_profil' => 'SlicerProfiles',
        'assembly_process' => 'AssemblyMethods',
        'quality_process' => 'QualityMethods'
    ];

    public function __construct(
        private readonly ParameterBagInterface $params,
        private readonly SluggerInterface $slugger,
        private readonly Filesystem $filesystem,
    ) {
        // Création des dossiers de base s'ils n'existent pas
        foreach (self::UPLOAD_FOLDERS as $folder) {
            $this->filesystem->mkdir($this->params->get('project_data_path') . '/' . $folder);
        }
    }

    public function handleFileUpload(UploadedFile $file, string $entityType = ''): string
    {
        $baseDir = $this->params->get('project_data_path');
        $uploadDir = $baseDir . '/' . (self::UPLOAD_FOLDERS[$entityType] ?? '');

        // Si le type d'entité n'est pas reconnu, utiliser le dossier de base
        if (!isset(self::UPLOAD_FOLDERS[$entityType])) {
            $uploadDir = $baseDir;
        }

        $fileName = uniqid($entityType . '_') . '.' . $file->guessExtension();
        $file->move($uploadDir, $fileName);

        return $uploadDir . '/' . $fileName;
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

        $this->filesystem->mkdir([
            $projectBasePath,
            $projectBasePath . '/Model',
            $projectBasePath . '/CustomerData',
        ]);

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
