<?php
// src/Form/FinishProcessAutocompleteType.php
namespace App\Form;

use App\Entity\Process\FinishProcess;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class FinishProcessAutocompleteType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => FinishProcess::class,
            'choice_label' => 'name',
            'multiple' => true,
            'label' => false,
            'tom_select_options' => [
                'placeholder' => 'Sélectionner ou créer des finitions'
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

