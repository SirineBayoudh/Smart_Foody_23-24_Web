<?php

namespace App\Controller;
use App\Entity\Produit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

class AccueilController extends AbstractController
{
    #[Route('/accueil', name: 'app_test')]
    public function index(): Response
    {
        
        return $this->render('accueil/index.html.twig', [
            'controller_name' => 'AccueilController',
        ]);
    }

    #[Route('/produit/accueil', name: 'accueil_produit')]
    public function afficherproduit(Request $request, PaginatorInterface $paginator): Response
    {
        // Récupérer les produits depuis la base de données
    $produits = $this->getDoctrine()->getRepository(Produit::class)->findAll();
    $pagination = $paginator->paginate(
        $produits, // Requête à paginer
        $request->query->getInt('page', 1), // Le numéro de page, 1 par défaut
        8 // Limite par page
    );

    // Passer les données de la pagination à la vue
    return $this->render('accueil/produit_accueil.html.twig', [
        'pagination' => $pagination,
    ]);
    }
}
