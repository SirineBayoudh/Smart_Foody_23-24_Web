<?php

namespace App\Controller;
use App\Entity\Produit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function afficherproduit(): Response
    {
        // Récupérer les produits depuis la base de données
        $produits = $this->getDoctrine()->getRepository(Produit::class)->findAll();

        // Passer les données des produits à la vue
        return $this->render('accueil/produit_accueil.html.twig', [
            'produits' => $produits,
        ]);
    }
}
