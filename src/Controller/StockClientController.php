<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Repository\ProduitRepository;
use App\Repository\StockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StockClientController extends AbstractController
{
    // #[Route('/stock/client', name: 'app_stock_client')]
    // public function index(): Response
    // {
    //     return $this->render('stock_client/index.html.twig', [
    //         'controller_name' => 'StockClientController',
    //     ]);
    // }


    #[Route('/stock/client', name: 'app_stock_client')]
    public function getFutureStocks(StockRepository $stockRepository): Response
    {
        // Récupérer les stocks à venir depuis le repository
        $futureStocks = $stockRepository->findFutureStocks();

        return $this->render('stock_client/index.html.twig', [
            'futureStocks' => $futureStocks,
        ]);
    }
    #[Route('/get-marques-by-category', name: 'get_marques_by_category')]
    public function getMarquesByCategory(Request $request, StockRepository $stockRepository): JsonResponse
    {
        $category = $request->query->get('category');

        // Récupérer les références des produits stockés dans la table Stock
        $stocks = $stockRepository->findRefProduits();

        // Récupérer les produits correspondant à ces références
        $produits = [];
        foreach ($stocks as $stock) {
            $produit = $stock->getRefProduit();
            if ($produit) { // Vérifier si le produit existe
                // Vérifier si le produit appartient à la catégorie spécifiée
                if ($produit->getCategorie() == $category) {
                    $produits[] = $produit;
                }
            }
        }

        // Récupérer les marques associées aux produits trouvés
        $marques = [];
        foreach ($produits as $produit) {
            $marque = $produit->getMarque(); // Accès à l'attribut "marque"
            if ($marque) { // Vérifier si la marque existe
                $marques[] = $marque; // Ajouter la marque à la liste des marques
            }
        }

        // Supprimer les doublons de marques
        $marques = array_unique($marques);

        return new JsonResponse(['marques' => $marques]);
    }
}
