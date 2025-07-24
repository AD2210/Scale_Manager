<?php

namespace App\Controller;

use App\Entity\CustomerData;
use App\Entity\Operation\CustomerDataOperation;
use App\Entity\Project;
use App\Repository\Base\SoftwareRepository;
use App\Repository\CustomerDataRepository;
use App\Repository\Operation\CustomerDataOperationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CustomerDataController extends AbstractController
{
    //@todo controller si effectivement utilisé
    #[Route('/datas/project{id}', name: 'app_datas_list')]
    public function customerDataList(Project $project, CustomerDataRepository $repo, SoftwareRepository $softwareRepository): Response
    {
        return $this->render('customerData/list.html.twig', [
            'items' => $repo->findBy(['project' => $project]),
            'softwares' => $softwareRepository->findBy(['isActive'=>true]),
        ]);
    }

    #[Route('/api/customer-data/{id}/operation', name: 'api_customer_data_operation_update', methods: ['POST'])]
    public function updateOperation(
        CustomerData $customerData,
        Request $request,
        EntityManagerInterface $em,
        CustomerDataOperationRepository $operationRepository,
        SoftwareRepository $softwareRepository
    ): JsonResponse {
        $content = json_decode($request->getContent(), true);

        if (!isset($content['software-id']) || !isset($content['value'])) {
            return $this->json(['error' => 'Missing required parameters'], Response::HTTP_BAD_REQUEST);
        }

        $software = $softwareRepository->find($content['software-id']);
        if (!$software) {
            return $this->json(['error' => 'Software not found'], Response::HTTP_NOT_FOUND);
        }

        $operation = $operationRepository->findOneBy([
            'customerData' => $customerData,
            'software' => $software
        ]);

        if (!$operation) {
            $operation = new CustomerDataOperation();
            $operation->setCustomerData($customerData);
            $operation->setSoftware($software);
        }

        $operation->setIsDone($content['value']);

        $em->persist($operation);
        $em->flush();

        return $this->json([
            'success' => true,
            'operation' => [
                'customerDataId' => $customerData->getId(),
                'softwareId' => $software->getId(),
                'isDone' => $operation->isIsDone()
            ]
        ]);
    }


}
