<?php

namespace App\Form;

use App\Entity\Objectif;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('libelle', ChoiceType::class, [
            'label' => 'Libellé',
            'choices' => [
                'Bien être' => 'Bien être',
                'Perte de poids' => 'Perte de poids',
                'Prise de poids' => 'Prise de poids',
                'Prise de masse musculaire' => 'Prise de masse musculaire',
                // Ajoutez ici d'autres options selon vos besoins
            ],
            'attr' => ['class' => 'form-control']
        ])
        
        ->add('listCritere', CheckboxType::class, [
            'label' => 'Critères',
            'choices' => [
                'Sans_lactose' => in_array('Sans_lactose', $options['selected_options']),
                'Protein' => in_array('Protein', $options['selected_options']),
                // Ajoutez d'autres options ici
            ],
            'mapped' => false, // Ne pas mapper ce champ à l'entité, car il est géré manuellement
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Objectif::class,
        ]);
    }
}
