<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Objectif;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProdRecommandeController extends AbstractController
{
    #[Route('/prod/recommande', name: 'app_prod_recommande')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur à partir de son ID (1 dans ce cas)
        $userId = 1; // ID de l'utilisateur dans la table utilisateur
        $utilisateur = $entityManager->getRepository(Utilisateur::class)->find($userId);

        // Vérifier si l'utilisateur existe
        if (!$utilisateur) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        // Récupérer l'objectif associé à l'utilisateur
        $objectifUtilisateur = $utilisateur->getObjectif();

        // Récupérer les produits dont le critère possède le même ID d'objectif que celui de l'utilisateur
        $produits = $entityManager->getRepository(Produit::class)->findBy(['critere' => $objectifUtilisateur]);

        return $this->render('prod_recommande/index.html.twig', [
            'controller_name' => 'ProdRecommandeController',
            'produits' => $produits,
        ]);
    }

}
