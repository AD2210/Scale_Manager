<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Builder\Class_;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EntityManagerService
{
    public function __construct(
        private readonly FileManagerService $fileManager,
        private EntityManagerInterface $em
    )
    {
    }

    public function updateEntityField($entity, string $field, mixed $value, array $config): void
    {
        $setter = 'set' . ucfirst($field);

        if (!method_exists($entity, $setter)) {
            return;
        }

        if (isset($config['fields_config'][$field])) {
            $fieldConfig = $config['fields_config'][$field];

            if ($fieldConfig['type'] === 'relation') {
                $this->updateEntityRelation($entity, $field, $value, $config);
                return;
            }

            if ($fieldConfig['type'] === 'enum') {
                $enumClass = $fieldConfig['class'];
                if ($value === null || $value === '') {
                    $entity->$setter(null);
                } else {
                    $enumValue = constant("$enumClass::$value");
                    $entity->$setter($enumValue);
                }
                return;
            }
        }

        if (in_array($field, ['fileLink', 'methodLink']) && $value instanceof UploadedFile) {
            // Récupérer le type d'entité à partir de la configuration
            $entityType = $this->getEntityTypeFromClass(get_class($entity));
            $value = $this->fileManager->handleFileUpload($value, $entityType);
        }

        if (in_array($field, ['isSpecific', 'isActive'])) {
            $value = (bool)$value;
        } elseif (is_string($value)) {
            $value = trim($value);
        }

        $entity->$setter($value);
    }

    private function getEntityTypeFromClass(string $className): string
    {
        // Conversion du nom de classe en type d'entité
        $map = [
            'App\Entity\Base\SlicerProfil' => 'slicer_profil',
            'App\Entity\Process\AssemblyProcess' => 'assembly_process',
            'App\Entity\Process\QualityProcess' => 'quality_process',
        ];

        return $map[$className] ?? '';
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

    public function updateEntityRelation($entity, string $field, mixed $value, array $config): void
    {
        if (!isset($config['fields_config'][$field]['entity'])) {
            return;
        }

        $setter = 'set' . ucfirst($field);
        if (!method_exists($entity, $setter)) {
            return;
        }

        if ($value === null) {
            $entity->$setter(null);
            return;
        }

        $relatedEntity = $this->em->getRepository($config['fields_config'][$field]['entity'])->find($value);
        if ($relatedEntity) {
            $entity->$setter($relatedEntity);
        }
    }

}
