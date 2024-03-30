<?php

namespace App\Form;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Objectif;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Repository\ObjectifRepository;


class ProduitType extends AbstractType
{
    private $objectifRepository;

    public function __construct(ObjectifRepository $objectifRepository)
    {
        $this->objectifRepository = $objectifRepository;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $criteres = $this->objectifRepository->findAll();

    // Initialisez un tableau pour stocker les choix de critères
    $choices = [];

    // Vérifiez si des critères ont été récupérés
    if (!empty($criteres)) {
        // Créez un tableau avec les libellés de tous les critères
        foreach ($criteres as $critere) {
            $choices[$critere->getId()] = $critere->getLibelle();
        }
    }

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
        ->add('image', FileType::class, [
            'label' => 'Image',
            'mapped' => true, // Ne pas mapper à une propriété de l'entité
            'required' => false, // Champ non obligatoire
        ])
        ->add('critere', EntityType::class, [
            'class' => Objectif::class,
            'choice_label' => 'listCritere', // ou tout autre attribut pour l'affichage
            'label' => 'Choisir un critère',
            'placeholder' => 'Sélectionnez un critère',
            'required' => false, // ou true selon vos besoins
            // Autres options...
        ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
