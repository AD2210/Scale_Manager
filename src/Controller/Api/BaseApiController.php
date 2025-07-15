<?php

namespace App\Controller\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\UnicodeString;

#[Route('/api')]
class BaseApiController extends AbstractController
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private string $projectDir,
        private readonly EntityManagerInterface $em
    ) {
    }

    #[Route('/create', name: 'api_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $className = $request->query->get('entity');

        if (!$className || !class_exists($className)) {
            return $this->json(['success' => false, 'error' => 'Entity class not found'], 400);
        }

        return $this->handleEntityCreation($request, $className);
    }

    #[Route('/update/{id}', name: 'api_update', methods: ['POST'])]
    public function update(Request $request, int $id): JsonResponse
    {
        $entityClass = $request->query->get('entity');
        if (!$entityClass || !class_exists($entityClass)) {
            return new JsonResponse(['error' => 'Classe introuvable'], Response::HTTP_BAD_REQUEST);
        }

        $entity = $this->em->getRepository($entityClass)->find($id);
        if (!$entity) {
            return new JsonResponse(['error' => 'Objet non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $field = $request->request->get('field');
        $value = $request->request->get('value');
        if (is_array($value)) {
            // cas spécial dropdown => reconstruire proprement avec entités
            $property = $this->normalizeFieldName($field);
            $targetClass = $this->guessRelationClass($entityClass, $property);

            $items = [];
            foreach ($value as $id => $checked) {
                if (filter_var($checked, FILTER_VALIDATE_BOOLEAN)) {
                    $related = $this->em->getRepository($targetClass)->find($id);
                    if ($related) {
                        $items[] = $related;
                    }
                }
            }
            $setter = 'set' . ucfirst($property);
            if (method_exists($entity, $setter)) {
                $entity->$setter($items);
            }
        } else {
            $property = $this->normalizeFieldName($field);
            $setter = 'set' . ucfirst($property);
            if (method_exists($entity, $setter)) {
                if ($value === 'true' || $value === 'false') {
                    $value = $value === 'true';
                }
                $entity->$setter($value);
            }
        }

        $this->em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/delete/{id}', name: 'api_delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        $entityClass = $request->query->get('entity');
        if (!$entityClass || !class_exists($entityClass)) {
            return new JsonResponse(['error' => 'Classe introuvable'], Response::HTTP_BAD_REQUEST);
        }

        $entity = $this->em->getRepository($entityClass)->find($id);

        if (!$entity) {
            return new JsonResponse(['error' => 'Objet non trouvé'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->em->remove($entity);
            $this->em->flush();
            return new JsonResponse(['success' => true]);
        } catch (\Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException) {
            return new JsonResponse(['error' => 'Conflit de dépendance'], Response::HTTP_CONFLICT);
        }
    }

    #[Route('/upload/{id}', name: 'api_upload', methods: ['POST'])]
    public function upload(Request $request, int $id): JsonResponse
    {
        $entityClass = $request->query->get('entity');
        $field = $request->request->get('field');
        $file = $request->files->get('file');

        if (!$entityClass || !class_exists($entityClass) || !$field || !$file instanceof UploadedFile) {
            return new JsonResponse(['error' => 'Paramètres invalides'], Response::HTTP_BAD_REQUEST);
        }

        $entity = $this->em->getRepository($entityClass)->find($id);
        if (!$entity) {
            return new JsonResponse(['error' => 'Objet non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $dir = '/uploads/' . strtolower((new \ReflectionClass($entity))->getShortName());
        $filename = $dir . '/' . uniqid() . '_' . $file->getClientOriginalName();

        $file->move($this->projectDir . '/public' . $dir, basename($filename));

        $setter = 'set' . ucfirst((new UnicodeString($field))->camel()->toString());
        if (!method_exists($entity, $setter)) {
            return new JsonResponse(['error' => 'Champ invalide'], Response::HTTP_BAD_REQUEST);
        }

        $entity->$setter($filename);
        $this->em->flush();

        return new JsonResponse(['success' => true]);
    }

    private function handleEntityCreation(Request $request, string $entityClass): JsonResponse
    {
        $entity = new $entityClass();
        $reflection = new \ReflectionClass($entityClass);

        foreach ($request->request->all() as $key => $value) {
            $property = $this->normalizeFieldName($key);

            if (is_array($value)) {
                $items = [];
                foreach ($value as $id => $checked) {
                    if (filter_var($checked, FILTER_VALIDATE_BOOLEAN)) {
                        $relatedEntity = $this->em->getRepository($this->guessRelationClass($entityClass, $property))->find($id);
                        if ($relatedEntity) {
                            $items[] = $relatedEntity;
                        }
                    }
                }

                $setter = 'set' . ucfirst($property);
                if ($reflection->hasMethod($setter)) {
                    $entity->$setter($items);
                }
            } else {
                $setter = 'set' . ucfirst($property);
                if ($reflection->hasMethod($setter)) {
                    if ($value === 'true' || $value === 'false') {
                        $value = $value === 'true';
                    }
                    $entity->$setter($value);
                }
            }
        }

        $this->em->persist($entity);
        $this->em->flush();

        return $this->json(['success' => true, 'id' => $entity->getId()]);
    }

    private function guessRelationClass(string $entityClass, string $fieldName): string
    {
        $metadata = $this->em->getClassMetadata($entityClass);
        $mapping = $metadata->getAssociationMapping($fieldName);

        return $mapping['targetEntity'] ?? throw new \LogicException("Unable to find targetEntity for $fieldName");
    }

    private function normalizeFieldName(string $key): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['new-', '-', '_'], ' ', $key))));
    }
}
