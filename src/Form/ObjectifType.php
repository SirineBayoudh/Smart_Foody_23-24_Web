<?php

namespace App\Form;

use App\Entity\Objectif;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ObjectifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('libelle', ChoiceType::class, [
            'choices' => [
                'Bien être' => 'Bien être',
                'Perte de poids' => 'Perte de poids',
                'Prise de poids' => 'Prise de poids',
                'Prise de masse musculaire' => 'Prise de masse musculaire',
            ],
            'label' => 'Objectif :',
            'required' => true,
            'placeholder' => 'Choisir un objectif',
        ])
        ->add('listCritere', ChoiceType::class, [
            'label' => 'Critères :',
            'required' => false,
            'mapped' => false, // Ne mappe pas directement cette propriété à une entité
            'choices' => [
                'sans_lactose' => 'sans_lactose',
                'sans_gluten' => 'sans_gluten',
                'sans_glucose' => 'sans_glucose',
                'protein' => 'protein',
            ],
            'multiple' => true, // Permet de sélectionner plusieurs critères
            'expanded' => true, // Affiche les choix sous forme de cases à cocher
            'constraints' => [
                new Callback([$this, 'validateListCritere']),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Objectif::class,
        ]);
    }
    public function validateListCritere($value, ExecutionContextInterface $context)
{
    // Si $value est un tableau vide ou null, cela signifie que aucune case n'est cochée
    if ($value === null || (is_array($value) && count($value) === 0)) {
        $context->buildViolation('Veuillez sélectionner au moins un critère.')
            ->addViolation();
    }
}
}
