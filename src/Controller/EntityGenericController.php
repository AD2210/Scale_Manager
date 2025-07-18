<?php

namespace App\Controller;

use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[Route('/generic', name: 'app_base_generic_')]
class EntityGenericController extends AbstractController
{
    /**
     * Liste des entités autorisées à être créées dynamiquement.
     */
    private const ALLOWED_ENTITIES = [
        'software' => [
            'class' => \App\Entity\Base\Software::class,
            'redirect_route' => 'app_software_list',
            'fields' => ['name', 'isActive']
        ],
        'customer' => [
            'class' => \App\Entity\Base\Customer::class,
            'redirect_route' => 'app_customer_list',
            'fields' => ['name', 'isActive']
        ],
        'manager' => [
            'class' => \App\Entity\Base\Manager::class,
            'redirect_route' => 'app_manager_list',
            'fields' => ['name', 'isActive']
        ],
        'sub_contractor' => [
            'class' => \App\Entity\Base\SubContractor::class,
            'redirect_route' => 'app_sub_contractor_list',
            'fields' => ['name', 'isActive']
        ],
        'slicer_profil' => [
            'class' => \App\Entity\Base\SlicerProfil::class,
            'redirect_route' => 'app_slicer_profil_list',
            'fields' => ['name', 'isActive', 'fileLink']
        ],
        'assembly_process' => [
            'class' => \App\Entity\Process\AssemblyProcess::class,
            'redirect_route' => 'app_assembly_process_list',
            'fields' => ['name', 'isActive', 'isSpecific', 'methodLink', 'comment']
        ],
        'finish_process' => [
            'class' => \App\Entity\Process\FinishProcess::class,
            'redirect_route' => 'app_finish_process_list',
            'fields' => ['name', 'isActive']
        ],
        'print3d_process' => [
            'class' => \App\Entity\Process\Print3DProcess::class,
            'redirect_route' => 'app_print3d_process_list',
            'fields' => ['name', 'isActive'],
            'manyToMany_fields' => [
                'treatmentProcess' => [
                    'entity' => \App\Entity\Process\TreatmentProcess::class,
                    'method' => 'addTreatmentProcess'
                ],
                'finishProcess' => [
                    'entity' => \App\Entity\Process\FinishProcess::class,
                    'method' => 'addFinishProcess'
                ]
            ]
        ],
        'print3d_material' => [
            'class' => \App\Entity\Process\Print3DMaterial::class,
            'redirect_route' => 'app_print3d_material_list',
            'fields' => ['name', 'isActive'],
            'manyToMany_fields' => [
                'treatmentProcess' => [
                    'entity' => \App\Entity\Process\TreatmentProcess::class,
                    'method' => 'addTreatmentProcess'
                ],
                'finishProcess' => [
                    'entity' => \App\Entity\Process\FinishProcess::class,
                    'method' => 'addFinishProcess'
                ]
            ]
        ],
        'quality_process' => [
            'class' => \App\Entity\Process\QualityProcess::class,
            'redirect_route' => 'app_quality_process_list',
            'fields' => ['name', 'isActive', 'methodLink', 'comment']
        ],
        'treatment_process' => [
            'class' => \App\Entity\Process\TreatmentProcess::class,
            'redirect_route' => 'app_treatment_process_list',
            'fields' => ['name', 'isActive']
        ],
        // ajoute d'autres entités ici
    ];

    public function __construct(
        private readonly ParameterBagInterface $params,
    )
    {
    }

    private function handleFileUpload(UploadedFile $file): string
    {
        $fileName = uniqid() . '.' . $file->guessExtension();
        $path = $this->params->get('project_data_path');
        $file->move($path, $fileName);

        return $path . '/' . $fileName;
    }

    private function updateEntityField($entity, string $field, mixed $value, array $config): void
    {
        $setter = 'set' . ucfirst($field);

        if (!method_exists($entity, $setter)) {
            return;
        }

        if (in_array($field, ['fileLink', 'methodLink'])) {
            if ($value instanceof UploadedFile) {
                $value = $this->handleFileUpload($value);
            }
        }

        if (in_array($field, ['isSpecific', 'isActive'])) {
            $value = (bool)$value;
        } elseif (is_string($value)) {
            $value = trim($value);
        }

        $entity->$setter($value);
    }

    private function updateManyToManyRelations($entity, string $field, mixed $value, array $relation, EntityManagerInterface $em): void
    {
        $repo = $em->getRepository($relation['entity']);
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

    #[Route('/update/{type}/{id}', name: 'app_base_generic_update', methods: ['PATCH', 'POST'])]
    public function update(Request $request, string $type, int $id, EntityManagerInterface $em): JsonResponse
    {
        // Vérifier si c'est une requête POST qui simule un PATCH
        if ($request->getMethod() === 'POST' && $request->headers->get('X-HTTP-Method-Override') === 'PATCH') {
            $request->setMethod('PATCH');
        }

        if (!isset(self::ALLOWED_ENTITIES[$type])) {
            return new JsonResponse(['error' => 'Type non autorisé'], 404);
        }

        $config = self::ALLOWED_ENTITIES[$type];
        $entity = $em->getRepository($config['class'])->find($id);

        if (!$entity) {
            return new JsonResponse(['error' => 'Entité introuvable'], 404);
        }

        // Gestion des fichiers
        $files = $request->files->all();
        dump($files);
        if (!empty($files)) {
            foreach ($files as $field => $file) {
                if (in_array($field, $config['fields'] ?? [])) {
                    $this->updateEntityField($entity, $field, $file, $config);
                }
            }
        } // Gestion des données JSON
        else {
            $data = $request->getContent() ? json_decode($request->getContent(), true) : [];
            foreach ($data as $field => $value) {
                if (in_array($field, $config['fields'] ?? [])) {
                    $this->updateEntityField($entity, $field, $value, $config);
                }
            }

            // Gestion des relations ManyToMany (uniquement pour les données JSON)
            foreach ($config['manyToMany_fields'] ?? [] as $field => $relation) {
                if (isset($data[$field])) {
                    $this->updateManyToManyRelations($entity, $field, $data[$field], $relation, $em);
                }
            }
        }

        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Mise à jour effectuée avec succès'
        ]);
    }

    #[Route('/create/{type}', name: 'create', methods: ['POST'])]
    public function create(
        string                 $type,
        Request                $request,
        EntityManagerInterface $em
    ): Response
    {
        if (!array_key_exists($type, self::ALLOWED_ENTITIES)) {
            throw $this->createNotFoundException('Type d’entité non autorisé');
        }

        $config = self::ALLOWED_ENTITIES[$type];
        $class = $config['class'];
        /** @var array $fields */
        $fields = $config['fields'];

        $entity = new $class();

        //Gestions des champs hors file
        foreach ($fields as $fieldName) {
            if ($fieldName === 'isSpecific' || $fieldName === 'isActive') {
                $value = $request->request->has($fieldName); // booléen
            } else {
                $value = trim($request->request->get($fieldName, ''));
            }

            if ($fieldName === 'fileLink' || $fieldName === 'methodLink') {
                /** @var UploadedFile|null $file */
                $file = $request->files->get($fieldName);
                if ($file) {
                    $fileName = uniqid() . '.' . $file->guessExtension();
                    $file->move($this->getParameter('project_data_path'), $fileName);
                    $value = $this->getParameter('project_data_path') . '/' . $fileName;
                }
            }

            // setter dynamique : setName, etc.
            $setter = 'set' . ucfirst($fieldName);
            if (method_exists($entity, $setter)) {
                $entity->$setter($value ?: null);
            }
        }

        foreach ($config['manyToMany_fields'] ?? [] as $field => $relation) {
            //dd($request->request->all(), $relation, $entity, $field, $relation['repo'], $relation['method']);
            $ids = $request->request->all($field); // tableau d’IDs
            $repo = $em->getRepository($relation['entity']);
            $addMethod = $relation['method'];

            foreach ((array)$ids as $id) {
                $related = $repo->find($id);
                if ($related && method_exists($entity, $addMethod)) {
                    $entity->$addMethod($related);
                }
            }
        }

        $em->persist($entity);
        $em->flush();

        $this->addFlash('success', ucfirst($type) . ' ajouté avec succès.');
        return $this->redirectToRoute($config['redirect_route']);
    }

    #[Route('/delete/{type}/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        string                 $type,
        int                    $id,
        EntityManagerInterface $em
    ): JsonResponse
    {
        try {
            if (!isset(self::ALLOWED_ENTITIES[$type])) {
                return new JsonResponse(['error' => 'Type non autorisé'], 404);
            }

            $config = self::ALLOWED_ENTITIES[$type];
            $entity = $em->getRepository($config['class'])->find($id);

            if (!$entity) {
                return new JsonResponse(['error' => 'Entité introuvable'], 404);
            }

            // Suppression des fichiers associés si nécessaire
            foreach (['fileLink', 'methodLink'] as $fileField) {
                if (method_exists($entity, 'get' . ucfirst($fileField))) {
                    $getter = 'get' . ucfirst($fileField);
                    $filePath = $entity->$getter();
                    if ($filePath && file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }

            $em->remove($entity);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Suppression effectuée avec succès'
            ]);
        } catch (ForeignKeyConstraintViolationException $e) {
            return new JsonResponse(
                [
                    'success' => false,
                    'error' => 'constraint',
                    'message' => 'Cet élément ne peut pas être supprimé car il est lié à d\'autres éléments'
                ],
                409
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                [
                    'success' => false,
                    'error' => 'Erreur lors de la suppression'
                ],
                500
            );
        }
    }
}
