<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileManagerService
{
    public function __construct(private readonly ParameterBagInterface $params)
    {
    }

    public function handleFileUpload(UploadedFile $file): string
    {
        $fileName = uniqid() . '.' . $file->guessExtension();
        $path = $this->params->get('project_data_path');
        $file->move($path, $fileName);

        return $path . '/' . $fileName;
    }

    public function deleteFile(?string $filePath): void
    {
        if ($filePath && file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
