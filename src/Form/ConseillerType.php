<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ConseillerType extends AbstractType
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
            ->add('matricule', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire',
                    ])
                ]])
            ->add('attestation', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire',
                    ])
                ]])
            ->add('photo')
            ->add('Ajouter', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary mr-2']
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class
        ]);
    }
}
