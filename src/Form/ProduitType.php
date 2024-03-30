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
use Doctrine\ORM\EntityRepository;


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

    $choices = [];

    // Vérifiez si des critères ont été récupérés
    if (!empty($criteres)) {
        // Parcourir chaque critère
        foreach ($criteres as $critere) {
            // Divisez la chaîne de critères en un tableau
            $critereList = explode(',', $critere->getListCritere());
            // Ajoutez chaque critère individuel au tableau des choix
            foreach ($critereList as $singleCritere) {
                $choices[$singleCritere] = $singleCritere;
            }
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
            'mapped' => false,
            'required' => false,
        ])
        ->add('critere', EntityType::class, [
            'class' => Objectif::class,
            'choice_label' => 'listCritere', // Attribut à afficher dans la liste déroulante
            'label' => 'Choisir un critère',
            'placeholder' => 'Sélectionnez un critère',
            'required' => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('o')
                    ->select('o')
                    ->distinct()
                    ->orderBy('o.listCritere', 'ASC');
            },
        ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
