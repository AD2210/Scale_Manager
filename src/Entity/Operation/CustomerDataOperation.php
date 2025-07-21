<?php

namespace App\Entity\Operation;

use App\Entity\Base\Software;
use App\Entity\CustomerData;
use App\Repository\Operation\CustomerDataOperationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerDataOperationRepository::class)]
class CustomerDataOperation
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: CustomerData::class, inversedBy: 'customerDataOperations')]
    #[ORM\JoinColumn(name: 'customer_data_id', referencedColumnName: 'id', nullable: false)]
    private CustomerData $customerData;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Software::class)]
    #[ORM\JoinColumn(name: 'software_id', referencedColumnName: 'id', nullable: false)]
    private Software $software;

    #[ORM\Column(type: 'boolean')]
    private bool $isDone = false;

    public function getCustomerData(): CustomerData
    {
        return $this->customerData;
    }

    public function setCustomerData(CustomerData $customerData): self
    {
        $this->customerData = $customerData;
        return $this;
    }

    public function getSoftware(): Software
    {
        return $this->software;
    }

    public function setSoftware(Software $software): self
    {
        $this->software = $software;
        return $this;
    }

    public function isIsDone(): bool
    {
        return $this->isDone;
    }

    public function setIsDone(bool $isDone): self
    {
        $this->isDone = $isDone;
        return $this;
    }
}

