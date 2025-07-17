<?php

namespace App\DataFixtures;

use App\Entity\Base\Customer;
use App\Entity\Base\Manager;
use App\Entity\Base\SlicerProfil;
use App\Entity\Base\Software;
use App\Entity\Base\SubContractor;
use App\Entity\Process\AssemblyProcess;
use App\Entity\Process\FinishProcess;
use App\Entity\Process\Print3DMaterial;
use App\Entity\Process\Print3DProcess;
use App\Entity\Process\QualityProcess;
use App\Entity\Process\TreatmentProcess;
use App\Entity\Project;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly ParameterBagInterface $params,
    )
    {
    }

    public function load(ObjectManager $om): void
    {
        $managers = $this->managerFixtures($om);
        $customers = $this->customerFixtures($om);
        $this->projectFixtures($om, $customers, $managers);
        $this->filesFixtures($om);
        $this->softwareFixtures($om);
        $this->slicerProfilFixtures($om);
        $this->subContractorFixtures($om);
        $finishes = $this->finishProcessFixtures($om);
        $treatments = $this->treatmentProcessFixtures($om);
        $assemblies = $this->assemblyProcessFixtures($om);
        $qualities = $this->qualityProcessFixtures($om);
        $materials = $this->print3DMaterialFixtures($om, $treatments, $finishes);
        $processes = $this->print3DProcessFixtures($om, $treatments, $finishes);
    }

    private function managerFixtures(ObjectManager $om): array
    {
        $managers = [];

        for ($i = 1; $i <= 5; $i++) {
            $manager = new Manager();
            $manager->setName("Manager $i");
            $manager->setIsActive(true);
            $managers[] = $manager;

            $om->persist($manager);
        }
        $om->flush();
        return $managers;
    }

    private function customerFixtures(ObjectManager $om): array
    {
        $customers = [];

        for ($i = 1; $i <= 5; $i++) {
            $customer = new Customer();
            $customer->setName("Client $i");
            $customer->setIsActive(true);
            $customers[] = $customer;

            $om->persist($customer);
        }
        $om->flush();
        return $customers;
    }

    public function projectFixtures(ObjectManager $om, array $customers, array $managers): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $project = new Project();
            $project->setTitle("Projet $i");
            $project->setCustomer($customers[rand(0, 4)]);
            $project->setManager($managers[rand(0, 4)]);
            $project->setIsArchived($i % 2 === 0); // alterne entre en cours / archivé
            $project->setDeadline(new DateTime("+$i week"));

            $om->persist($project);
        }
        $om->flush();
    }

    public function filesFixtures(ObjectManager $om): void
    {
        $filesystem = new Filesystem();
        $basePath = $this->params->get('project_data_path');

        $projects = $om->getRepository(Project::class)->findAll();

        foreach ($projects as $project) {
            $baseName = $project->getId() . $project->getTitle();
            $projectDir = "$basePath/$baseName";
            $modelDir = "$basePath/$baseName/Model";
            $dataDir = "$basePath/$baseName/CustomerData";

            $filesystem->mkdir([$projectDir, $modelDir, $dataDir]);
            $project->setModelLink("$basePath/$baseName/Model");
            $project->setCustomerDataLink("$basePath/$baseName/CustomerData");
            file_put_contents("$basePath/$baseName/quote.pdf", "%PDF-1.4 fake");
            $project->setQuoteLink("$basePath/$baseName/quote.pdf");
            file_put_contents("$basePath/$baseName/specs.pdf", "%PDF-1.4 fake");
            $project->setSpecificationLink("$basePath/$baseName/specs.pdf");
            $om->persist($project);

            for ($i = 1; $i <= 3; $i++) {
                file_put_contents("$modelDir/model_$i.stl", "fake STL content");
                file_put_contents("$dataDir/data_$i.step", "fake STEP content");
            }
        }
        $om->flush();
    }

    private function softwareFixtures(ObjectManager $om): void
    {
        $names = ['Fusion 360', 'Meshmixer', 'Netfabb', 'Blender', 'SolidWorks'];

        foreach ($names as $name) {
            $software = new Software();
            $software->setName($name);
            $software->setIsActive(true);

            $om->persist($software);
        }
        $om->flush();
    }

    private function slicerProfilFixtures(ObjectManager $om): void
    {
        $names = ['Standard FDM', 'Fine SLA', 'HighSpeed DLP'];
        foreach ($names as $name) {
            $profile = new SlicerProfil();
            $profile->setName($name);
            $profile->setIsActive(true);

            $om->persist($profile);
        }
        $om->flush();
    }

    private function subContractorFixtures(ObjectManager $om): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $sub = new SubContractor();
            $sub->setName("Sous-traitant $i");
            $sub->setIsActive(true);

            $om->persist($sub);
        }
        $om->flush();
    }

    private function print3DProcessFixtures(ObjectManager $om, array $treatments, array $finishes): array
    {
        $processes = [];

        $labels = ['FDM', 'SLA', 'DLP', 'SLS'];
        foreach ($labels as $label) {
            $proc = new Print3DProcess();
            $proc->setName($label);
            $proc->setIsActive(true);
            $proc->addTreatmentProcess($treatments[rand(0, 2)]);
            $proc->addFinishProcess($finishes[rand(0, 2)]);

            $processes[] = $proc;
            $om->persist($proc);
        }

        $om->flush();
        return $processes;
    }

    private function finishProcessFixtures(ObjectManager $om): array
    {
        $finishes = [];

        $labels = ['Polissage', 'Peinture', 'Vernis'];
        foreach ($labels as $label) {
            $finish = new FinishProcess();
            $finish->setName($label);
            $finish->setIsActive(true);

            $finishes[] = $finish;
            $om->persist($finish);
        }

        $om->flush();
        return $finishes;
    }

    private function assemblyProcessFixtures(ObjectManager $om): array
    {
        $assemblies = [];

        $labels = ['Collage', 'Emboitage', 'Vissage'];
        foreach ($labels as $label) {
            $assembly = new AssemblyProcess();
            $assembly->setName($label);
            $assembly->setIsActive(true);
            $assembly->setIsSpecific(false);
            $assembly->setComment('commentaire :' .$label);

            $assemblies[] = $assembly;
            $om->persist($assembly);
        }

        $om->flush();
        return $assemblies;
    }

    private function qualityProcessFixtures(ObjectManager $om): array
    {
        $qualities = [];

        $labels = ['Visuel', 'Colorimétrie', 'Dimensionnel'];
        foreach ($labels as $label) {
            $quality = new QualityProcess();
            $quality->setName($label);
            $quality->setIsActive(true);

            $qualities[] = $quality;
            $om->persist($quality);
        }

        $om->flush();
        return $qualities;
    }

    private function treatmentProcessFixtures(ObjectManager $om): array
    {
        $treatments = [];

        $labels = ['Ebavurage', 'Ponçage', 'Perçage'];
        foreach ($labels as $label) {
            $treatment = new TreatmentProcess();
            $treatment->setName($label);
            $treatment->setIsActive(true);

            $treatments[] = $treatment;
            $om->persist($treatment);
        }

        $om->flush();
        return $treatments;
    }

    private function print3DMaterialFixtures(ObjectManager $om, array $treatments, array $finishes): array
    {
        $materials = [];

        $labels = ['PLA', 'ABS', 'Résine grise', 'Nylon'];
        foreach ($labels as $i => $label) {
            $material = new Print3DMaterial();
            $material->setName($label);
            $material->setIsActive(true);
            $material->addTreatmentProcess($treatments[rand(0, 2)]);
            $material->addFinishProcess($finishes[rand(0, 2)]);


            $materials[] = $material;
            $om->persist($material);
        }

        $om->flush();
        return $materials;
    }
}
