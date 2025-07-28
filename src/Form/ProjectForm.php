<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\Base\Customer;
use App\Entity\Base\Manager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProjectForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class,[
                'label' => 'Titre',
                'required' => true,
            ])
            ->add('customer', EntityType::class, [
                'label' => 'Client',
                'class' => Customer::class,
                'choice_label' => 'name',
            ])
            ->add('manager', EntityType::class, [
                'label' => 'Responsable',
                'class' => Manager::class,
                'choice_label' => 'name',
                'required' => false,
            ])
            ->add('deadline', DateType::class, [
                'label' => 'Date limite',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('quoteLink', FileType::class, [
                'required' => false,
                'label' => 'Devis (PDF ou image)',
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/*'
                        ],
                    ])
                ],
            ])
            ->add('specificationLink', FileType::class, [
                'required' => false,
                'label' => 'Spécifications techniques',
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/*'
                        ],
                    ])
                ],
            ])
            ->add('customerDataLink', TextType::class, [
                'label' => 'Dossier données client',
                'required' => false,
            ])
            ->add('modelLink', TextType::class, [
                'label' => 'Dossier modèles',
                'required' => false,
            ])
            ->add('isArchived', CheckboxType::class, [
                'label' => 'Projet archivé ?',
                'required' => false,
            ])
            ->add('isQualityOk', CheckboxType::class, [
                'label' => 'Qualité validée',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
