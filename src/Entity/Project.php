<?php

namespace App\Entity;

use App\Entity\Base\Customer;
use App\Entity\Base\Manager;
use App\Entity\Process\QualityProcess;
use App\Repository\ProjectRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    private ?Manager $Manager = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTime $deadline = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $quoteLink = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $specificationLink = null;

    /**
     * @var Collection<int, CustomerData>
     */
    #[ORM\OneToMany(targetEntity: CustomerData::class, mappedBy: 'project')]
    private Collection $customerData;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customerDataLink = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $modelLink = null;

    #[ORM\Column]
    private ?bool $isArchived = false;

    #[ORM\Column]
    private ?bool $isQualityOk = false;

    /**
     * @var Collection<int, QualityProcess>
     */
    #[ORM\ManyToMany(targetEntity: QualityProcess::class, inversedBy: 'projects')]
    private Collection $qualityProcess;

    public function __construct()
    {
        $this->customerData = new ArrayCollection();
        $this->qualityProcess = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getManager(): ?Manager
    {
        return $this->Manager;
    }

    public function setManager(?Manager $Manager): static
    {
        $this->Manager = $Manager;

        return $this;
    }

    public function getDeadline(): ?DateTime
    {
        return $this->deadline;
    }

    public function setDeadline(?DateTime $deadline): static
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getQuoteLink(): ?string
    {
        return $this->quoteLink;
    }

    public function setQuoteLink(?string $quoteLink): static
    {
        $this->quoteLink = $quoteLink;

        return $this;
    }

    public function getSpecificationLink(): ?string
    {
        return $this->specificationLink;
    }

    public function setSpecificationLink(?string $specificationLink): static
    {
        $this->specificationLink = $specificationLink;

        return $this;
    }

    /**
     * @return Collection<int, CustomerData>
     */
    public function getCustomerData(): Collection
    {
        return $this->customerData;
    }

    public function addCustomerData(CustomerData $customerData): static
    {
        if (!$this->customerData->contains($customerData)) {
            $this->customerData->add($customerData);
            $customerData->setProject($this);
        }

        return $this;
    }

    public function removeCustomerData(CustomerData $customerData): static
    {
        if ($this->customerData->removeElement($customerData)) {
            // set the owning side to null (unless already changed)
            if ($customerData->getProject() === $this) {
                $customerData->setProject(null);
            }
        }

        return $this;
    }

    public function getCustomerDataLink(): ?string
    {
        return $this->customerDataLink;
    }

    public function setCustomerDataLink(string $customerDataLink): static
    {
        $this->customerDataLink = $customerDataLink;

        return $this;
    }

    public function getModelLink(): ?string
    {
        return $this->modelLink;
    }

    public function setModelLink(string $modelLink): static
    {
        $this->modelLink = $modelLink;

        return $this;
    }

    public function isArchived(): ?bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(bool $isArchived): static
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    public function isQualityOk(): ?bool
    {
        return $this->isQualityOk;
    }

    public function setIsQualityOk(bool $isQualityOk): static
    {
        $this->isQualityOk = $isQualityOk;

        return $this;
    }

    /**
     * @return Collection<int, QualityProcess>
     */
    public function getQualityProcess(): Collection
    {
        return $this->qualityProcess;
    }

    public function addQualityProcess(QualityProcess $qualityProcess): static
    {
        if (!$this->qualityProcess->contains($qualityProcess)) {
            $this->qualityProcess->add($qualityProcess);
        }

        return $this;
    }

    public function removeQualityProcess(QualityProcess $qualityProcess): static
    {
        $this->qualityProcess->removeElement($qualityProcess);

        return $this;
    }
}
