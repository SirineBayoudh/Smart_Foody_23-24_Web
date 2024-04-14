<?php

namespace App\Form;

use App\Entity\Objectif;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ClientType extends AbstractType
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
                ],
                'constraints' => [
                    new NotBlank(['message' => 'champ obligatoire'])
                ]
            ])
            ->add('email')
            ->add('motDePasse', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire',
                    ])
                ]])
            ->add('numTel', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire',
                    ])
                ]])
            ->add('adresse', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire',
                    ])
                ]])
            ->add('objectif', EntityType::class, [
                'class' => Objectif::class,
                'choice_label' => 'libelle', 
                'placeholder' => 'Sélectionnez un objectif',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'champ obligatoire'])
                ]
            ])
            ->add('taille', NumberType::class, [
                'attr' => ['class' => 'form-control spinner', 'id' => 'taille'],
                'constraints' => [
                    new NotBlank(['message' => 'champ obligatoire'])
                ],
                'data' => 170
            ])
            ->add('poids', NumberType::class, [
                'attr' => ['class' => 'form-control spinner', 'id' => 'poids'],
                'constraints' => [
                    new NotBlank(['message' => 'champ obligatoire'])
                ],
                'data' => 70
            ])
            ->add('photo')
            ->add('Inscription', SubmitType::class, [
                'attr' => ['class' => 'btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn', 'id' => 'submitBtn' ,'style' => 'background-color: #56ab2f; border-color:#56ab2f']
            ])
            ->setAttributes(['id' => '1']);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class
        ]);
    }
}
