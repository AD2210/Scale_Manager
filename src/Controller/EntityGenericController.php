<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Array_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
            'fields' => ['name', 'isActive'] //@todo voir pour ajout des process de traitement et de finition par défault
        ],
        'print3d_material' => [
            'class' => \App\Entity\Process\Print3DMaterial::class,
            'redirect_route' => 'app_print3d_material_list',
            'fields' => ['name', 'isActive'] //@todo voir pour ajout des process de traitement et de finition par défault
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

    #[Route('/create/{type}', name: 'create', methods: ['POST'])]
    public function create(
        string $type,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        if (!array_key_exists($type, self::ALLOWED_ENTITIES)) {
            throw $this->createNotFoundException('Type d’entité non autorisé');
        }

        $config = self::ALLOWED_ENTITIES[$type];
        $class = $config['class'];
        /** @var array $fields */
        $fields = $config['fields'];

        $entity = new $class();

        foreach ($fields as $fieldName) {
            $value = trim($request->request->get($fieldName, ''));
            if ($fieldName === 'name' && $value === '') {
                $this->addFlash('danger', 'Le champ "Nom" est requis.');
                return $this->redirectToRoute($config['redirect_route']);
            }

            // setter dynamique : setName, setCode, etc.
            $setter = 'set' . ucfirst($fieldName);
            if (method_exists($entity, $setter)) {
                $entity->$setter($value ?: null);
            }
        }

        $em->persist($entity);
        $em->flush();

        $this->addFlash('success', ucfirst($type) . ' ajouté avec succès.');
        return $this->redirectToRoute($config['redirect_route']);
    }
}
