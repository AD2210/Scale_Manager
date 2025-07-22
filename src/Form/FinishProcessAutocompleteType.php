<?php
// src/Form/FinishProcessAutocompleteType.php
namespace App\Form;

use App\Entity\Process\FinishProcess;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;
use Symfony\UX\Autocomplete\Form\ParentEntityAutocompleteType;

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
            'attr' => [
                'data-controller' => 'symfony--ux-autocomplete--autocomplete',
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

