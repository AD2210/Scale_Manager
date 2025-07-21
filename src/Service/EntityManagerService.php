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


        if (in_array($field, ['fileLink', 'methodLink'])) {
            if ($value instanceof UploadedFile) {
                $value = $this->fileManager->handleFileUpload($value);
            }
        }

        if (in_array($field, ['isSpecific', 'isActive'])) { //@todo vérifier que le test Bool ne sert à rien car Update
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
