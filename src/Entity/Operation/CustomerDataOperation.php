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
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $isDone = false;

    #[ORM\ManyToOne]
    private ?Software $software = null;

    #[ORM\ManyToOne(inversedBy: 'customerDataOperations')]
    private ?CustomerData $customerData = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isDone(): ?bool
    {
        return $this->isDone;
    }

    public function setIsDone(bool $isDone): static
    {
        $this->isDone = $isDone;

        return $this;
    }

    public function getSoftware(): ?Software
    {
        return $this->software;
    }

    public function setSoftware(?Software $software): static
    {
        $this->software = $software;

        return $this;
    }

    public function getCustomerData(): ?CustomerData
    {
        return $this->customerData;
    }

    public function setCustomerData(?CustomerData $customerData): static
    {
        $this->customerData = $customerData;

        return $this;
    }
}
