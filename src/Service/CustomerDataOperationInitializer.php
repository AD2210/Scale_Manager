<?php

namespace App\Service;

use App\Entity\Base\Software;
use App\Entity\CustomerData;
use App\Entity\Operation\CustomerDataOperation;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;

class CustomerDataOperationInitializer
{
    private EntityRepository $customerDataRepo;
    private EntityRepository $softwareRepo;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em){
        $this->em = $em;
        $this->customerDataRepo = $em->getRepository(CustomerData::class);
        $this->softwareRepo = $em->getRepository(Software::class);
    }

    // Initialize les opérations sur données client en fct des softwares renseignés
    public function init(Project $project) : void
    {
        $customerDatas = $this->customerDataRepo->findBy(['project' => $project]);
        $softwares = $this->softwareRepo->findAll();

        foreach ($customerDatas as $customerData) {
            // Récupérer les opérations existantes pour ce CustomerData
            $existingOperations = [];
            foreach ($customerData->getCustomerDataOperations() as $operation) {
                $key = $operation->getSoftware()->getId();
                $existingOperations[$key] = true;
            }

            foreach ($softwares as $software) {
                // Vérifier si l'opération existe déjà
                if (!isset($existingOperations[$software->getId()])) {
                    try {
                        $dataOperation = new CustomerDataOperation();
                        $dataOperation->setCustomerData($customerData);
                        $dataOperation->setSoftware($software);
                        $dataOperation->setIsDone(false);
                        $this->em->persist($dataOperation);

                        // Ajouter à la collection de CustomerData
                        $customerData->addCustomerDataOperation($dataOperation);
                    } catch (Exception $e) {
                        // Log l'erreur, mais continue le processus
                        error_log(sprintf(
                            "Erreur lors de l'initialisation de l'opération pour CustomerData ID: %d, Software ID: %d - %s",
                            $customerData->getId(),
                            $software->getId(),
                            $e->getMessage()
                        ));
                    }
                }
            }
        }

        try {
            $this->em->flush();
        } catch (Exception $e) {
            error_log("Erreur lors de la sauvegarde des opérations: " . $e->getMessage());
            throw $e; // Remonter l'erreur pour la gestion au niveau supérieur
        }
    }


}
