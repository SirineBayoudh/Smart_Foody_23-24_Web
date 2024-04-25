<?php

namespace App\Form;

use App\Entity\Objectif;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

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
                ],
                'constraints' => [
                    new NotBlank(['message' => 'champ obligatoire'])
                ]
            ])
            ->add('email', TextType::class, [
                'constraints' => [
                    new Email([
                        'message' => 'L\'adresse email n\'est pas de la forme ***@gmail.com.',

                    ]),
                ],
            ])
            ->add('numTel', NumberType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire',
                    ]),
                    new Length([
                        'min' => 8,
                        'max' => 8,
                        'exactMessage' => 'Le numéro de téléphone doit avoir exactement {{ limit }} chiffres',
                        'normalizer' => 'trim',
                    ]),
                    new Regex([
                        'pattern' => '/^\d+$/',
                        'message' => 'Le numéro de téléphone doit contenir uniquement des chiffres.',
                    ]),
                ],
                'invalid_message' => 'Le numéro de téléphone doit contenir uniquement des chiffres'
            ])
            ->add('adresse', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire',
                    ])
                ]
            ])
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
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire',
                    ])
                ]
            ])
            ->add('poids', NumberType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire',
                    ])
                ]
            ])
            ->add('photo', FileType::class, [
                'data_class' => null,
                'label' => 'Photo (fichier image)',
                'required' => false,

                'attr' => [
                    'accept' => 'image/*', // Cela limite le gestionnaire de fichiers à montrer seulement les images
                ]
            ])
            ->add('Modifier', SubmitType::class, [
                'attr' => ['class' => 'btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn', 'id' => 'submitBtn', 'style' => 'background-color: #56ab2f; border-color:#56ab2f']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class
        ]);
    }
}
