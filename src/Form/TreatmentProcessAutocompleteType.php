<?php
// src/Form/TreatmentProcessAutocompleteType.php
namespace App\Form;

use App\Entity\Process\TreatmentProcess;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class TreatmentProcessAutocompleteType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => TreatmentProcess::class,
            'choice_label' => 'name',
            'multiple' => true,
            'label' => false,
            'tom_select_options' => [
                'placeholder' => 'Sélectionner ou créer des traitements'
            ],
            'query_builder' => function($repository) {
                return $repository->createQueryBuilder('t')
                    ->where('t.isActive = :active')
                    ->setParameter('active', true)
                    ->orderBy('t.name', 'ASC');
            }
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}



