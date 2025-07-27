<?php

namespace App\Controller;

use App\Entity\Base\SlicerProfil;
use App\Entity\Process\AssemblyProcess;
use App\Entity\Process\QualityProcess;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ViewController extends AbstractController
{
    #[Route('/view/slicer-profil/{id}', name: 'app_slicer_profil_view')]
    public function viewSlicerProfil(SlicerProfil $profil): Response
    {
        return $this->serveFile($profil->getFileLink());
    }

    #[Route('/view/assembly-process/{id}', name: 'app_assembly_process_view')]
    public function viewAssembly(AssemblyProcess $assembly): Response
    {
        //dd($this->serveFile($assembly->getMethodLink()));
        return $this->serveFile($assembly->getMethodLink());
    }

    #[Route('/view/quality-process/{id}', name: 'app_quality_process_view')]
    public function viewQuality(QualityProcess $quality): Response
    {
        return $this->serveFile($quality->getMethodLink());
    }

    private function serveFile(?string $path): Response
    {
        if (!$path) {
            throw $this->createNotFoundException('Aucun fichier associé.');
        }

        if (!file_exists($path)) {
            throw $this->createNotFoundException('Fichier introuvable.');
        }

        /** @noinspection UseControllerShortcuts */
        return new BinaryFileResponse($path);
    }
}
