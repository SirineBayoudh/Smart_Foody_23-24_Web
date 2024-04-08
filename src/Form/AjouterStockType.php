<?php

namespace App\Form;

use App\Entity\Stock;
use App\Repository\ProduitRepository;
use App\Repository\StockRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;

class AjouterStockType extends AbstractType
{
    private $stockRepository;
    private $produitRepository;

    // public function __construct(StockRepository $stockRepository)
    // {
    //     $this->stockRepository = $stockRepository;
    // }
    public function __construct(ProduitRepository $produitRepository)
    {
        $this->produitRepository = $produitRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $marques = $this->produitRepository->findAllDistinctMarques(); // Récupérer les marques distinctes depuis StockRepository
        var_dump($marques);
        $builder
            ->add('nom')
            ->add('ref_produit')
            ->add('marque', ChoiceType::class, [
                'choices' => $marques,
                'placeholder' => 'Sélectionnez une marque', // Optionnel : affiche un placeholder
            ])
            ->add('quantite')
            ->add('date_arrivage', DateTimeType::class, [
                'date_widget' => 'single_text'
            ]);

        // ->add('submit', SubmitType::class, [
        //     'attr' => ['class' => 'btn btn-primary mr-2'],
        // ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}
