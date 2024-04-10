<?php

namespace App\Form;

use App\Entity\Reclamation;
use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType; // Correction du namespace
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class ReclamationEnvoyerType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('nom', TextType::class, [
            'data' => $options['user']->getNom(),
            'attr' => ['readonly' => true, 'class' => 'form-control'], // Le champ sera en lecture seule
            'label' => 'Nom : ', // Ajouter le libellé pour le champ nom
        ])
        // Les autres champs du formulaire...
        ->add('prenom', TextType::class, [
            'data' => $options['user']->getPrenom(),
            'attr' => ['readonly' => true],
            'label' => 'Prénom : ',
        ])
        ->add('email', TextType::class, [
            'data' => $options['user']->getEmail(),
            'attr' => ['readonly' => true],
            'label' => 'Email :',
        ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Réclamation' => 'Réclamation',
                    "Demande d'information" => "Demande d'information",
                    "Remerciement" => "Remerciement",
                    "Demande de Collaboration" => "Demande de Collaboration",
                    "Autres" => "Autres",
                ],
                'label' => 'Type :',
        
            ])
            ->add('titre', TextType::class, [
                'label' => 'Titre :',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Votre message :',
                
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults([
                'data_class' => Reclamation::class,
                'user' => null, // Assurez-vous que cette option est définie et peut être null si nécessaire
            ]);
        }
}