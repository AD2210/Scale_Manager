<?php

namespace App\Service;

use App\Entity\Base\Software;
use App\Entity\CustomerData;
use App\Entity\Operation\CustomerDataOperation;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

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
    public function init(Project $project) : void{
        $customerDatas = $this->customerDataRepo->findBy(['project' => $project]);
        $softwares = $this->softwareRepo->findAll();
        foreach ($customerDatas as $customerData) {
            foreach ($softwares as $software) {
                $dataOperation = new CustomerDataOperation();
                $dataOperation->setCustomerData($customerData);
                $dataOperation->setSoftware($software);
                $this->em->persist($dataOperation);
            }
        }
        $this->em->flush();
    }

}
