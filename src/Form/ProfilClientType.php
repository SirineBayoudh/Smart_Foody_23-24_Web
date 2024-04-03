<?php

namespace App\Form;

use App\Entity\Objectif;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfilClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('genre', ChoiceType::class, [
                'choices' => 
                [
                    'Femme' => 'Femme',
                    'Homme' => 'Homme',
                ]
            ])
            ->add('email')
            ->add('numTel')
            ->add('adresse')
            ->add('objectif', EntityType::class, [
                'class' => Objectif::class,
                'choice_label' => 'libelle', 
                'placeholder' => 'Sélectionnez un objectif',
                'required' => true,
            ])
            ->add('taille')
            ->add('poids')
            ->add('photo')
            ->add('Modifier', SubmitType::class, [
                'attr' => ['class' => 'btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn', 'id' => 'submitBtn' ,'style' => 'background-color: #56ab2f; border-color:#56ab2f']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
            'validation_groups' => ['Client']
        ]);
    }
}
