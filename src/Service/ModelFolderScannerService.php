<?php

namespace App\Service;

use App\Entity\Model;
use App\Entity\Project;
use App\Repository\CustomerDataRepository;
use App\Repository\ModelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ModelFolderScannerService
{
    private EntityManagerInterface $em;
    private Filesystem $filesystem;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->filesystem = new Filesystem();
    }

    /**
     * Synchronise les fichiers physiques du dossier Model avec la base de données.
     *
     * @param Project $project
     * @param bool $deleteOrphans Si true, supprime les entités sans fichier correspondant
     * @return array<string, array<string>> Statistiques (dev uniquement) : [added, deleted, skipped]
     */
    public function scan(Project $project, bool $deleteOrphans = false): array
    {
        $stats = [
            'added' => [],
            'deleted' => [],
            'skipped' => [],
        ];

        $directory = $project->getModelLink();
        if (!$directory || !$this->filesystem->exists($directory)) {
            return $stats; // Aucun dossier à scanner
        }

        $existing = $this->em->getRepository(Model::class)->findBy(['project' => $project]);
        $existingMap = [];

        foreach ($existing as $entity) {
            $existingMap[$entity->getFileName()] = $entity;
        }

        $finder = new Finder();
        $finder->files()->in($directory);

        foreach ($finder as $file) {
            $filename = $file->getFilename();

            if (!isset($existingMap[$filename])) {
                $model = new Model();
                $model->setFileName($filename);
                $model->setProject($project);

                $this->em->persist($model);
                $stats['added'][] = $filename;
            } else {
                $stats['skipped'][] = $filename;
                unset($existingMap[$filename]); // Non orphelin
            }
        }

        if ($deleteOrphans) {
            foreach ($existingMap as $filename => $entity) {
                $this->em->remove($entity);
                $stats['deleted'][] = $filename;
            }
        }

        $this->em->flush();

        return $stats;
    }
}
