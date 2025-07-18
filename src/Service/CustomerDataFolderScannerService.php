<?php

namespace App\Service;

use App\Entity\CustomerData;
use App\Entity\Project;
use App\Repository\CustomerDataRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class CustomerDataFolderScannerService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CustomerDataRepository $repository,
        private Filesystem             $filesystem = new Filesystem()
    ) {
    }

    /**
     * Synchronise les fichiers d'un dossier avec les entités CustomerData.
     *
     * @param Project $project
     * @param bool $deleteMissingEntities Si true, supprime les entités orphelines.
     * @return array Retourne ['added' => int, 'deleted' => int, 'skipped' => int]
     */
    public function scan(Project $project, bool $deleteMissingEntities = false): array
    {
        $added = 0;
        $deleted = 0;
        $skipped = 0;

        $folderPath = $project->getCustomerDataLink();
        if (!$folderPath || !$this->filesystem->exists($folderPath)) {
            return ['exit 1'];
        }

        $finder = new Finder();
        $finder->files()->in($folderPath);

        // Index des fichiers présents
        $realFiles = [];
        foreach ($finder as $file) {
            $realFiles[] = $file->getFilename();
        }

        // Récupère les entités existantes
        $existingEntities = $this->repository->findBy(['project' => $project]);
        $existingMap = [];
        foreach ($existingEntities as $entity) {
            $existingMap[$entity->getFileName()] = $entity;
        }

        // Ajout ou Skip
        foreach ($realFiles as $fileName) {
            if (!isset($existingMap[$fileName])) {
                $entity = new CustomerData();
                $entity->setProject($project);
                $entity->setFileName($fileName);

                $this->em->persist($entity);
                $added++;
            } else {
                $skipped++;
                unset($existingMap[$fileName]); // On l'enlève pour la détection d'orphelins
            }
        }

        // Suppression éventuelle des entités orphelines
        if ($deleteMissingEntities) {
            foreach ($existingMap as $orphanEntity) {
                $this->em->remove($orphanEntity);
                $deleted++;
            }
        }
        // On enregistre les fichier en bdd
        $this->em->flush();

        // On initialise les opérations par rapport aux fichiers ajoutés
        $initializer = new CustomerDataOperationInitializer($this->em);
        $initializer->init($project);

        return [
            'added' => $added,
            'deleted' => $deleted,
            'skipped' => $skipped
        ];
    }
}
