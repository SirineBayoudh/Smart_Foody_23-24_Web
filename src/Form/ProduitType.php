<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('marque', TextType::class, [
            'label' => 'Marque : ',
            'attr' => ['placeholder' => 'Marque']
        ])
        ->add('categorie', ChoiceType::class, [
            'label' => 'Catégorie : ',
            'choices' => [
                'Fruit' => 'Fruit',
                'Legume' => 'Legume',
                'Laitier' => 'Laitier',
                'Grain' => 'Grain',
            ],
            'placeholder' => 'Choisir une catégorie'
        ])
        ->add('prix', TextType::class, [
            'label' => 'Prix : ',
            'attr' => ['placeholder' => 'Prix']
        ])
        ->add('listCritere', TextType::class, [
            'label' => 'Liste des critères (séparés par des virgules)',
            // Autres options de configuration
        ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
