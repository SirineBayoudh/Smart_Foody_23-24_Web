<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('search', TextType::class, [
                'label' => false,
                'required' => false, // Désactivation de la requête HTML5
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ de recherche ne peut pas être vide.', // Message personnalisé
                    ]),
                    new Regex([
                        'pattern' => '/^(\d{4}-\d{2}-\d{2})|([a-zA-Z\s]+)$/i',
                        'message' => 'Entrez une date valide (YYYY-MM-DD) ou un nom (lettres et espaces uniquement).'
                    ])
                ],
                'attr' => [
                    'placeholder' => 'YYYY-MM-DD ou Nom',
                    'class' => 'form-control',
                    'autocomplete' => 'off'  // Désactivation de l'autocomplétion du navigateur
                ]
                ]);
          
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
            'csrf_protection' => true,
            'method' => 'GET',
        ]);
    }
}
