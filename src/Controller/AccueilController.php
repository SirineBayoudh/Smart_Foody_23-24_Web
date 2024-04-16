<?php

namespace App\Controller;
use App\Entity\Produit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMappingBuilder;


class AccueilController extends AbstractController
{
    #[Route('/accueil', name: 'app_test')]
    public function index(Request $request, PaginatorInterface $paginator ): Response
    {
            $term = $request->query->get('term');
    
            if ($term) {
                // Si un terme de recherche est spécifié, ne récupérez pas tous les produits
                $produits = []; // Ne récupérez aucun produit
            } else {
                // Sinon, récupérez tous les produits comme avant
                $category = $request->query->get('category');
    
                if ($category) {
                    // Si une catégorie est sélectionnée, récupérez les produits filtrés par cette catégorie
                    $produits = $this->getDoctrine()->getRepository(Produit::class)->findBy(['categorie' => $category]);
                } else {
                    // Sinon, récupérez tous les produits
                    $produits = $this->getDoctrine()->getRepository(Produit::class)->findAll();
                }
            }
    
            // Pagination des produits
            $pagination = $paginator->paginate(
                $produits, // Requête à paginer
                $request->query->getInt('page', 1), // Le numéro de page, 1 par défaut
                8 // Limite par page
            );
    
            return $this->render('accueil/index.html.twig', [
                'controller_name' => 'AccueilController',
                'pagination' => $pagination,
            ]);
        
    }

    #[Route('/filter-products/{category}', name: 'filter_products_by_category')]
    public function filterProductsByCategory($category , ProduitRepository $productRepository) : Response
    {
        // Logique pour récupérer les produits filtrés en fonction de la catégorie
        $filteredProducts = $productRepository->findByCategory($category);; // Récupérez les produits filtrés de votre base de données ou d'où vous les stockez
        
        // Renvoyer les produits filtrés au format HTML (par exemple, en utilisant un rendu de Twig)
        return $this->render('path_to_your_template/filtered_products.html.twig', [
            'filteredProducts' => $filteredProducts,
        ]);
    }

    #[Route('/autocomplete', name: 'autocomplete')]
public function autocomplete(Request $request, EntityManagerInterface $entityManager): JsonResponse
{
    $term = $request->query->get('term');

    $query = $entityManager->createQueryBuilder()
        ->select('p')
        ->from(Produit::class, 'p')
        ->where('p.marque LIKE :term')
        ->setParameter('term', '%' . $term . '%')
        ->getQuery();

    $products = $query->getResult();

    $formattedProducts = [];
    foreach ($products as $product) {
        $formattedProducts[] = [
            'marque' => $product->getMarque(),
            'image' => $product->getImage(),
            'prix' => $product->getPrix(),
        ];
    }

    return $this->json($formattedProducts);
}

#[Route('/rechercher-produits', name: 'rechercher_produits')]
public function rechercherProduits(Request $request, PaginatorInterface $paginator, EntityManagerInterface $entityManager): Response
{
    $term = $request->query->get('term');

    $query = $entityManager->createQueryBuilder()
        ->select('p')
        ->from(Produit::class, 'p')
        ->where('p.marque LIKE :term')
        ->setParameter('term', '%' . $term . '%')
        ->getQuery();

    $produits = $query->getResult();

    // Pagination des produits
    $pagination = $paginator->paginate(
        $produits, // Requête à paginer
        $request->query->getInt('page', 1), // Le numéro de page, 1 par défaut
        8 // Limite par page
    );

    return $this->render('accueil/index.html.twig', [
        'controller_name' => 'AccueilController',
        'pagination' => $pagination,
    ]);
}
    
}
