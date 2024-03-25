<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Entity\Stock;
use App\Form\AjouterStockType;
use App\Repository\ProduitRepository;
use App\Repository\StockRepository;
use Doctrine\DBAL\Types\TextType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;

class StockController extends AbstractController
{
    #[Route('/stock', name: 'stock_get')]
    public function getStock(StockRepository $stockRepo, ProduitRepository $produitRepo, EntityManagerInterface $entityManager, FlashBagInterface $flashBag): Response
    {
        // Récupérez les données de la table Stock et Produit
        $stocks = $stockRepo->findAll();
        $produits = $produitRepo->findAll();

        // Parcourez les stocks pour effectuer la vérification et afficher une alerte si nécessaire
        foreach ($stocks as $stock) {
            // Vérifiez si nbVendu est nul
            if ($stock->getNbvendu() === null) {
                // Affectez la valeur 0 à nbVendu
                $stock->setNbvendu(0);
            }

            // Vérifiez si nbVendu est égal à quantite

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
}
