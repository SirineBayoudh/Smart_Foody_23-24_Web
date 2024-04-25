<?php

namespace App\Form;

use App\Entity\Utilisateur;
use League\OAuth2\Client\Grant\Password;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

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
            ->add('email', TextType::class, [
                'constraints' => [
                    new Email([
                        'message' => 'L\'adresse email n\'est pas de la forme ***@gmail.com.',

                    ]),
                ],
            ])
            ->add('motDePasse', PasswordType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire',
                    ])
                ]
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
            ->add('matricule', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire',
                    ])
                ]
            ])
            ->add('attestation', FileType::class, [
                'data_class' => null,
                'constraints' => [
                    new NotBlank([
                        'message' => 'champ obligatoire',
                    ]),
                    new File([
                        'maxSize' => '1024k',  // Taille maximale du fichier
                        'mimeTypes' => [
                            'application/pdf',  // Type MIME pour les fichiers PDF
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF valide',
                    ])
                ],
                'attr' => [
                    'accept' => '.pdf',  // Restreindre les téléchargements aux fichiers .pdf dans l'interface utilisateur
                    'class' => 'custom-file-input'
                ]
            ])
            ->add('photo', FileType::class, [
                'data_class' => null,
                'label' => 'Photo (fichier image)',
                'required' => false,

                'attr' => [
                    'accept' => 'image/*', // Cela limite le gestionnaire de fichiers à montrer seulement les images
                    'class' => 'custom-file-input' 
                ]
            ])
            ->add('Ajouter', SubmitType::class, [
                'attr' => ['class' => 'btn btn-success mr-2']
            ]);;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class
        ]);
    }
}
