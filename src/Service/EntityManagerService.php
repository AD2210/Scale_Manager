<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EntityManagerService
{
    public function __construct(
        private readonly FileManagerService $fileManager,
        private readonly EntityManagerInterface $em
    ) {
    }

    public function updateEntityField($entity, string $field, mixed $value, array $config): void
    {
        $setter = 'set' . ucfirst($field);

        if (!method_exists($entity, $setter)) {
            return;
        }

        if (in_array($field, ['fileLink', 'methodLink'])) {
            if ($value instanceof UploadedFile) {
                $value = $this->fileManager->handleFileUpload($value);
            }
        }

        if (in_array($field, ['isSpecific', 'isActive'])) {
            $value = (bool)$value;
        } elseif (is_string($value)) {
            $value = trim($value);
        }

        $entity->$setter($value);
    }

    public function updateManyToManyRelations($entity, string $field, mixed $value, array $relation): void
    {
        $repo = $this->em->getRepository($relation['entity']);
        $method = $relation['method'];
        $getter = 'get' . ucfirst($field);

        if (method_exists($entity, $getter)) {
            $current = $entity->$getter();
            if (method_exists($current, 'clear')) {
                $current->clear();
            }
        }

        foreach ((array)$value as $relId) {
            if ($related = $repo->find($relId)) {
                $entity->$method($related);
            }
        }
    }
}
