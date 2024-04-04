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
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $produits = $this->getDoctrine()->getRepository(Produit::class)->findAll();
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
