<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class ProfilController extends AbstractController
{
    private $utilisateurRepository;

    public function __construct(UtilisateurRepository $utilisateurRepository)
    {
        $this->utilisateurRepository = $utilisateurRepository;
    }

    #[Route('/profil/{id}', name: 'app_profil')]
    public function index(int $id, CommandeRepository $commandeRepository): Response
    {
        $id_client = 14;

        $utilisateur = $this->utilisateurRepository->find($id_client);
        

        // Récupérer l'historique des commandes du client avec l'ID passé en paramètre
        $historiqueCommandes = $commandeRepository->findHistoriqueCommandesByClientId($id_client);

        return $this->render('profil/index.html.twig', [
            'historiqueCommandes' => $historiqueCommandes,
            
        ]);
    }

 /**
 * @Route("/commande/{id}", name="commande_details")
 */
public function commandeDetails($id, CommandeRepository $commandeRepository)
{
    $commande = $commandeRepository->find($id);

    if (!$commande) {
        throw $this->createNotFoundException('La commande demandée n\'existe pas');
    }

    // Récupérer les lignes de commande associées à la commande
    $lignesCommande = $commande->getLignesCommande();

    return $this->render('profil/details_commande.html.twig', [
        'commande' => $commande,
        'lignesCommande' => $lignesCommande, // Assurez-vous que cette variable est bien passée au template
    ]);
}

    

}
