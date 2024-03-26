<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProduitRepository;
use Dompdf\Dompdf;
use Dompdf\Options;

class ProduitController extends AbstractController
{



#[Route('/produits', name: 'liste_produits')]
public function listeProduits(ProduitRepository $produitRepository): Response
{
    $produits = $produitRepository->findAll();
    return $this->render('produit/index.html.twig', [
        'produits' => $produits,
    ]);
}


}