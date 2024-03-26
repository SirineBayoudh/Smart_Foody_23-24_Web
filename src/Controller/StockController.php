<?php

namespace App\Controller;

use App\Entity\Alerte;
use App\Entity\Produit;
use App\Entity\Stock;
use App\Form\AjouterStockType;
use App\Repository\ProduitRepository;
use App\Repository\StockRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StockController extends AbstractController
{
    // #[Route('/stock', name: 'stock_get')]
    // public function getStock(StockRepository $stockRepo, ProduitRepository $produitRepo, EntityManagerInterface $entityManager, FlashBagInterface $flashBag): Response
    // {
    //     // Récupérez les données de la table Stock et Produit
    //     $stocks = $stockRepo->findAll();
    //     $produits = $produitRepo->findAll();

    //     // Parcourez les stocks pour effectuer la vérification et afficher une alerte si nécessaire
    //     foreach ($stocks as $stock) {
    //         // Vérifiez si nbVendu est nul
    //         if ($stock->getNbvendu() === null) {
    //             // Affectez la valeur 0 à nbVendu
    //             $stock->setNbvendu(0);
    //         }

    //         // Vérifiez si nbVendu est égal à quantite
    //         if ($stock->getNbvendu() === $stock->getQuantite()) {
    //             // Ajoutez un message flash avec une classe pour l'alerte rouge
    //             $flashBag->add('danger', 'La quantité vendue est égale à la quantité en stock pour le produit ' . $stock->getNom());
    //         }
    //     }

    //     // Parcourez les stocks pour calculer et mettre à jour le coût pour chaque stock
    //     foreach ($stocks as $stock) {
    //         // Récupérez la marque et la quantité du stock actuel
    //         $stockMarque = $stock->getMarque();
    //         $quantite = $stock->getQuantite();

    //         // Recherchez le produit correspondant dans la table Produit
    //         foreach ($produits as $produit) {
    //             // Si la marque du stock correspond à la marque du produit
    //             if ($produit->getMarque() === $stockMarque) {
    //                 // Récupérez le prix du produit
    //                 $prix = $produit->getPrix();

    //                 // Calculez le coût en multipliant la quantité par le prix
    //                 $cout = $quantite * $prix;

    //                 // Mettez à jour le coût dans l'entité Stock
    //                 $stock->setCout($cout);

    //                 // Enregistrez les modifications dans la base de données
    //                 $entityManager->flush();

    //                 // Sortez de la boucle car nous avons trouvé le produit correspondant
    //                 break;
    //             }
    //         }
    //     }

    //     // Passez les stocks à la vue
    //     return $this->render('stock/index.html.twig', [
    //         'stocks' => $stocks,
    //     ]);
    // }

    #[Route('/stock', name: 'stock_get')]
    public function getStock(
        StockRepository $stockRepo,
        ProduitRepository $produitRepo,
        EntityManagerInterface $entityManager,
        FlashBagInterface $flashBag
    ): Response {
        // Récupérez les données de la table Stock et Produit
        $stocks = $stockRepo->findExistantStocks();
        $produits = $produitRepo->findAll();

        $alertMessages = []; // Tableau pour stocker les alertes temporaires

        // Parcourez les stocks pour effectuer la vérification et créer une alerte si nécessaire
        foreach ($stocks as $stock) {
            // Vérifiez si nbVendu est nul
            if ($stock->getNbvendu() === null) {
                // Affectez la valeur 0 à nbVendu
                $stock->setNbvendu(0);
            }

            // Vérifiez si nbVendu est égal à quantite
            if ($stock->getNbvendu() === $stock->getQuantite()) {
                // Créez une nouvelle instance d'Alerte
                $alerte = new Alerte();
                $alerte->setDescription_alerte('le stock ' . $stock->getNom() . ' est en rupture');
                $alerte->setDateAlerte(new \DateTime()); // Date actuelle
                $alerte->setType(false); // Mettez le type d'alerte à faux

                // Persistez l'entité Alerte
                $entityManager->persist($alerte);

                // Ajoutez la description de l'alerte au tableau des messages
                $alertMessages[] = $alerte->getDescription_alerte();
            }
        }

        // Ajoutez les alertes au FlashBag
        foreach ($alertMessages as $message) {
            $flashBag->add('danger', $message);
        }

        // Parcourez les stocks pour calculer et mettre à jour le coût pour chaque stock
        foreach ($stocks as $stock) {
            // Récupérez la marque et la quantité du stock actuel
            $stockMarque = $stock->getMarque();
            $quantite = $stock->getQuantite();

            // Recherchez le produit correspondant dans la table Produit
            foreach ($produits as $produit) {
                // Si la marque du stock correspond à la marque du produit
                if ($produit->getMarque() === $stockMarque) {
                    // Récupérez le prix du produit
                    $prix = $produit->getPrix();

                    // Calculez le coût en multipliant la quantité par le prix
                    $cout = $quantite * $prix;

                    // Mettez à jour le coût dans l'entité Stock
                    $stock->setCout($cout);

                    // Enregistrez les modifications dans la base de données
                    $entityManager->flush();

                    // Sortez de la boucle car nous avons trouvé le produit correspondant
                    break;
                }
            }
        }

        // Flush toutes les entités persistées
        $entityManager->flush();

        // Passez les stocks à la vue
        return $this->render('stock/index.html.twig', [
            'stocks' => $stocks,
        ]);
    }


    #[Route('/deleteStock/{id}', name: 'stock_delete')]
    public function deleteStock(ManagerRegistry $manager, $id, StockRepository $repo): Response
    {
        $stock = $repo->find($id);
        $em = $manager->getManager();
        $em->remove($stock);
        $em->flush();
        return $this->redirectToRoute("stock_get");
    }
    #[Route('/edit_stock/{id}', name: 'stock_edit')]
    public function editstock(Request $req, ManagerRegistry $manager, $id, StockRepository $repo): Response
    {
        $j = $repo->find($id);
        $form = $this->createFormBuilder($j)
            ->add('nom', TextType::class, ['disabled' => true]) // Rend le champ 'nom' non éditable
            // ->add('ref_produit', TextType::class, ['disabled' => true]) // Rend le champ 'ref_produit' non éditable
            ->add('marque', TextType::class, ['disabled' => true]) // Rend le champ 'marque' non éditable
            ->add('quantite') // Champ quantite reste éditable
            ->add('date_arrivage') // Champ quantite reste éditable
            ->add('submit', SubmitType::class)
            ->getForm();
        $form->handleRequest($req);
        $em = $manager->getManager();
        if ($form->isSubmitted()  && $form->isValid()) {

            $em->persist($j);  // juste préparer les requetes
            $em->flush();

            return $this->redirectToRoute("stock_get");
        }
        return  $this->renderForm(
            'stock/ajouter.html.twig',
            [
                'form' => $form,
                //'stocks' => $repo->findAll() // Passer les stocks à la vue
            ]
        );
    }



    #[Route('/ajouter/stock', name: 'app_ajouter_stock')]
    public function index(Request $request, ManagerRegistry $manager, ProduitRepository $produitRepository): Response
    {
        $stock = new Stock();
        $form = $this->createForm(AjouterStockType::class, $stock);
        $form->handleRequest($request);
        $em = $manager->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer la marque sélectionnée dans le formulaire
            $marque = $stock->getMarque();

            // Rechercher l'objet Produit correspondant à la marque sélectionnée
            $produit = $produitRepository->findOneBy(['marque' => $marque]);

            // Vérifier si un produit correspondant a été trouvé
            if ($produit instanceof Produit) {
                // Récupérer la référence exacte du produit
                $stock->setRefProduit($produit);
            }
            // Persister l'objet Stock dans la base de données
            $em->persist($stock);
            $em->flush();

            // Rediriger l'utilisateur vers la page de liste des stocks ou une autre page appropriée
            return $this->redirectToRoute('stock_get');
        }

        return $this->render('stock/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/stock_venir', name: 'stock_venir')]
    public function getFutureStocks(StockRepository $stockRepository): Response
    {
        // Récupérer les stocks à venir depuis le repository
        $futureStocks = $stockRepository->findFutureStocks();

        return $this->render('stock/future_stocks.html.twig', [
            'futureStocks' => $futureStocks,
        ]);
    }
}
