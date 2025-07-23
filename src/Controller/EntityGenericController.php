<?php

namespace App\Controller;

use App\Service\EntityManagerService;
use App\Service\FileManagerService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/generic', name: 'app_base_generic_')]
class EntityGenericController extends AbstractController
{
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
        'model' => [
            'class' => \App\Entity\Model::class,
            'fields' => [
                'filName',
                'isNeedTest',
                'isReadyToPrint',
                'slicerProfil',
                'isSubContracted',
                'subcontractor',
                'quantity',
                'isDelivered',
                'print3dProcess',
                'print3dMaterial',
                'print3dStatus'
            ],
            'fields_config' => [
                'slicerProfil' => [
                    'entity' => \App\Entity\Base\SlicerProfil::class,
                    'type' => 'relation'
                ],
                'print3dProcess' => [
                    'entity' => \App\Entity\Process\Print3DProcess::class,
                    'type' => 'relation'
                ],
                'print3dMaterial' => [
                    'entity' => \App\Entity\Process\Print3DMaterial::class,
                    'type' => 'relation'
                ],
                'print3dStatus' => [
                    'type' => 'enum',
                    'class' => \App\Entity\Enum\Print3DStatusEnum::class
                ]
            ]
        ],
        // ajoute d'autres entités ici
    ];

    public function __construct(
        private readonly EntityManagerService   $entityService,
        private readonly FileManagerService     $fileService,
        private readonly EntityManagerInterface $em
    )
    {
    }

    private function validateEntityType(string $type): ?JsonResponse
    {
        if (!isset(self::ALLOWED_ENTITIES[$type])) {
            return new JsonResponse(['error' => 'Type non autorisé'], 404);
        }
        return null;
    }

    private function getEntity(string $type, ?int $id = null)
    {
        $config = self::ALLOWED_ENTITIES[$type];
        return $id ? $this->em->getRepository($config['class'])->find($id) : new $config['class']();
    }

    #[Route('/update/{type}/{id}', name: 'update', methods: ['PATCH', 'POST'])]
    public function update(Request $request, string $type, int $id): JsonResponse
    {
        if ($response = $this->validateEntityType($type)) {
            return $response;
        }

        try {
            $config = self::ALLOWED_ENTITIES[$type];
            $entity = $this->getEntity($type, $id);

            if (!$entity) {
                return new JsonResponse(['error' => 'Entité introuvable'], 404);
            }

            // Traitement des fichiers
            foreach ($request->files->all() as $field => $file) {
                if (in_array($field, $config['fields'] ?? [])) {
                    $this->entityService->updateEntityField($entity, $field, $file, $config);
                }
            }

            // Traitement des données JSON
            if ($jsonData = json_decode($request->getContent(), true)) {

                foreach ($jsonData as $field => $value) {
                    if (in_array($field, $config['fields'] ?? [])) {
                        $this->entityService->updateEntityField($entity, $field, $value, $config);
                    }
                }

                foreach ($config['manyToMany_fields'] ?? [] as $field => $relation) {
                    if (isset($jsonData[$field])) {
                        $this->entityService->updateManyToManyRelations($entity, $field, $jsonData[$field], $relation);
                    }
                }
            }

            $this->em->flush();
            return new JsonResponse(['success' => true]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/create/{type}', name: 'create', methods: ['POST'])]
    public function create(Request $request, string $type): JsonResponse
    {
        if ($response = $this->validateEntityType($type)) {
            return $response;
        }

        try {
            /** @var array $config */
            $config = self::ALLOWED_ENTITIES[$type];
            $entity = $this->getEntity($type);

            // Traitement des champs simples
            foreach ($config['fields'] as $field) {
                $value = $request->request->get($field) ?? $request->files->get($field);
                $this->entityService->updateEntityField($entity, $field, $value, $config);
            }

            // Traitement des relations ManyToMany
            foreach ($config['manyToMany_fields'] ?? [] as $field => $relation) {
                $ids = $request->request->all($field);
                $this->entityService->updateManyToManyRelations($entity, $field, $ids, $relation);
            }

            $this->em->persist($entity);
            $this->em->flush();

            return new JsonResponse([
                'success' => true
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/delete/{type}/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $type, int $id): JsonResponse
    {
        if ($response = $this->validateEntityType($type)) {
            return $response;
        }

        try {
            $entity = $this->getEntity($type, $id);

            if (!$entity) {
                return new JsonResponse(['error' => 'Entité introuvable'], 404);
            }

            // Suppression des fichiers associés
            foreach (['fileLink', 'methodLink'] as $fileField) {
                if (method_exists($entity, 'get' . ucfirst($fileField))) {
                    $this->fileService->deleteFile($entity->{'get' . ucfirst($fileField)}());
                }
            }

            $this->em->remove($entity);
            $this->em->flush();

            return new JsonResponse(['success' => true]);

        } catch (ForeignKeyConstraintViolationException) {
            return new JsonResponse([
                'success' => false,
                'error' => 'constraint',
                'message' => 'Cet élément ne peut pas être supprimé car il est lié à d\'autres éléments'
            ], 409);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }


}
