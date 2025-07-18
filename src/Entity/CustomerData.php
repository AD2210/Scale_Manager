<?php

namespace App\Entity;

use App\Entity\Operation\CustomerDataOperation;
use App\Repository\CustomerDataRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerDataRepository::class)]
class CustomerData
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $fileName = null;

    #[ORM\ManyToOne(inversedBy: 'customerData')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    /**
     * @var Collection<int, CustomerDataOperation>
     */
    #[ORM\OneToMany(targetEntity: CustomerDataOperation::class, mappedBy: 'customerData')]
    private Collection $customerDataOperations;

    public function __construct()
    {
        $this->customerDataOperations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return Collection<int, CustomerDataOperation>
     */
    public function getCustomerDataOperations(): Collection
    {
        return $this->customerDataOperations;
    }

    public function addCustomerDataOperation(CustomerDataOperation $customerDataOperation): static
    {
        if (!$this->customerDataOperations->contains($customerDataOperation)) {
            $this->customerDataOperations->add($customerDataOperation);
            $customerDataOperation->setCustomerData($this);
        }

        return $this;
    }

    public function removeCustomerDataOperation(CustomerDataOperation $customerDataOperation): static
    {
        if ($this->customerDataOperations->removeElement($customerDataOperation)) {
            // set the owning side to null (unless already changed)
            if ($customerDataOperation->getCustomerData() === $this) {
                $customerDataOperation->setCustomerData(null);
            }
        }

        return $this;
    }
}
