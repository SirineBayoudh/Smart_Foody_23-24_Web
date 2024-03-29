<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\JsonResponse;
use Twig\Environment as TwigEnvironment;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use App\Repository\LigneCommandeRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\CommandeRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\EntityManagerInterface;










class PanierController extends AbstractController
{
    private $commandeRepository;
    private $produitRepository;
    private $panierRepository;
    private $utilisateurRepository;
    private $ligneCommandeRepository;
    
    private $entityManager;

    public function __construct(UtilisateurRepository $utilisateurRepository ,CommandeRepository $commandeRepository,PanierRepository $panierRepository, ProduitRepository $produitRepository, LigneCommandeRepository $ligneCommandeRepository, EntityManagerInterface $entityManager)
    {

        $this->utilisateurRepository = $utilisateurRepository;
        $this->commandeRepository = $commandeRepository;
        $this->panierRepository = $panierRepository;
        $this->produitRepository = $produitRepository;
        $this->ligneCommandeRepository = $ligneCommandeRepository;
        $this->entityManager = $entityManager;
    }



    #[Route('/panier/supprimer-ligne/{idLigneCommande}', name: 'supprimer_ligne_panier')]
    public function supprimerLignePanier(Request $request, $idLigneCommande): Response
    {
        $ligneCommande = $this->ligneCommandeRepository->find($idLigneCommande);
        
        if ($ligneCommande) {
            $this->entityManager->remove($ligneCommande);
            $this->entityManager->flush();
    
            $panier = $ligneCommande->getPanier();
            $total = 0.0;
            foreach ($panier->getLignesCommande() as $ligne) {
                $total += $ligne->getQuantite() * $ligne->getProduit()->getPrix();
            }
            $panier->setTotale($total);
    
            // Ici, on récupère l'ID du client de la session ou un ID fixe comme exemple
            $idClient = 14; // Vous pouvez adapter cette partie pour récupérer l'ID du client de façon dynamique
            $nombreDeCommandes = $this->commandeRepository->countCommandesByClientId($idClient);
    
            // Définition de la période de remise et du nombre de commandes pour le calcul de la remise
            $dateActuelle = new \DateTime();
            $dateDebutRemise = new \DateTime('2024-01-01');
            $dateFinRemise = new \DateTime('2024-02-01');
            $dateDansPeriodeRemise = $dateActuelle >= $dateDebutRemise && $dateActuelle < $dateFinRemise;
    
            if ($dateDansPeriodeRemise) {
                $remise = $total * 0.5;
            } else {
                if ($nombreDeCommandes >= 3 && $nombreDeCommandes <= 9) {
                    $remise = $total * 0.15;
                } elseif ($nombreDeCommandes > 9) {
                    $remise = $total * 0.25;
                } else {
                    $remise = 0;
                }
            }
    
            $prixTotalAvantRemise = $total;
            $prixTotalApresRemise = $prixTotalAvantRemise - $remise;
    
            $this->entityManager->persist($panier);
            $this->entityManager->flush();
            $nombreArticlesDansPanier = count($panier->getLignesCommande());
    
            // Retourne la vue avec les données mises à jour
            return $this->render('panier/index.html.twig', [
                'lignesCommande' => $panier->getLignesCommande(),
                'prixTotalAvantRemise' => $prixTotalAvantRemise,
                'remise' => $remise,
                'prixTotalApresRemise' => $prixTotalApresRemise,
                'nombreArticlesDansPanier' => $nombreArticlesDansPanier,
            ]);
        }
    
        return $this->redirectToRoute('voir_panier');
    }
    



/**
 * @Route("/panier/vider", name="vider_panier")
 */
#[Route('/panier/vider', name: 'vider_panier')]
public function viderPanier(Request $request): Response
{
    $session = $request->getSession();
    $panierId = $session->get('panier_id');
    if ($panierId) {
        $panier = $this->panierRepository->find($panierId);
        foreach ($panier->getLignesCommande() as $ligne) {
            $this->entityManager->remove($ligne);
        }
        $this->entityManager->flush();
    }


      $panier->setTotale(0.0);

       $this->entityManager->persist($panier);
       $this->entityManager->flush();

    return $this->redirectToRoute('voir_panier');
}








#[Route('/panier/ajouter/{ref}', name: 'ajouter_produit_panier')]
public function ajouterProduitPanier(Request $request, $ref): Response
{
    $id_client=13;


   


    $utilisateur = $this->utilisateurRepository->find($id_client);
    $session = $request->getSession();
    $panierId = $session->get('panier_id');

    if (!$panierId) {
        $panier = new Panier();
    

        $this->entityManager->persist($panier);
        $this->entityManager->flush();

        $session->set('panier_id', $panier->getId_panier());
    } else {
        $panier = $this->panierRepository->find($panierId);
        if (!$panier) {
            $panier = new Panier();
            $panier->setUtilisateur($utilisateur);
            $this->entityManager->persist($panier);
            $this->entityManager->flush();
            $session->set('panier_id', $panier->getId_panier());
        }
    }

    $produit = $this->produitRepository->find($ref);
    if (!$produit) {
        return $this->redirectToRoute('produit_non_trouve');
    }

    $ligneCommande = $this->ligneCommandeRepository->findOneBy(['panier' => $panier, 'produit' => $produit]);

    if ($ligneCommande) {
        $ligneCommande->setQuantite($ligneCommande->getQuantite() );
    } else {
        $ligneCommande = new LigneCommande();
        $ligneCommande->setProduit($produit);
        $ligneCommande->setPanier($panier);
        $ligneCommande->setQuantite(1);
    }

    $this->entityManager->persist($ligneCommande);
    $this->entityManager->flush();

    // Mise à jour du total du panier
    $total = 0.0;
    foreach ($panier->getLignesCommande() as $ligne) {
        $total += $ligne->getQuantite() * $ligne->getProduit()->getPrix();


    }
    $panier->setTotale($total);

    $this->entityManager->persist($panier);
    $this->entityManager->flush();

 

if (!$utilisateur) {
    throw $this->createNotFoundException('Utilisateur non trouvé pour l\'ID '.$id_client);
}
    $commande = new Commande();
    $commande->setDateCommande(new \DateTime());
  
$commande->setUtilisateur($utilisateur);

   
    $commande->setTotaleCommande($panier->getTotale());
    $commande->setRemise(0);
    $commande->setEtat('non validé');


    foreach ($panier->getLignesCommande() as $ligneCommande) {
    $ligneCommande->setCommande($commande); 
    $this->entityManager->persist($ligneCommande);
   }




  $nombreDeCommandes = $this->commandeRepository->countCommandesByClientId($id_client);


$dateActuelle = new \DateTime();


$dateDebutRemise = new \DateTime('2024-01-01');
$dateFinRemise = new \DateTime('2024-02-01');
$dateDansPeriodeRemise = $dateActuelle >= $dateDebutRemise && $dateActuelle < $dateFinRemise;


// Calcul de la remise
if ($dateDansPeriodeRemise) {
$remise = $panier->getTotale() * 0.5;
} else {
if ($nombreDeCommandes >= 3 && $nombreDeCommandes <= 9) {
    $remise = $panier->getTotale() * 0.15;
} elseif ($nombreDeCommandes > 9) {
    $remise = $panier->getTotale() * 0.25;
} else {
    $remise = 0;
}
}

// Calcul du prix total avant remise
$prixTotalAvantRemise = $panier->getTotale();

// Calcul du prix total après remise
$prixTotalApresRemise = $prixTotalAvantRemise - $remise;

// Appliquer la remise à la commande
$commande->setRemise($remise);


$this->entityManager->persist($commande);
$this->entityManager->flush();

$nombreArticlesDansPanier = count($panier->getLignesCommande());

// Passage des variables à la vue Twig
return $this->render('panier/index.html.twig', [
'lignesCommande' => $panier->getLignesCommande(),
'prixTotalAvantRemise' => $prixTotalAvantRemise,
'remise' => $remise,
'prixTotalApresRemise' => $prixTotalApresRemise,
'nombreArticlesDansPanier' => $nombreArticlesDansPanier,


]);
}


    

    #[Route('/commande/annuler/{id}', name: 'cANU', methods: ['GET', 'POST'])]
    public function test(EntityManagerInterface $entityManager, Request $request, $id,ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();
    
        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
           
        ]);
        
        }



/**
 * @Route("/panier/augmenter_quantite/{idLigneCommande}", name="augmenter_quantite")
 */
public function augmenterQuantite($idLigneCommande): Response
{
    $ligneCommande = $this->ligneCommandeRepository->find($idLigneCommande);

    if ($ligneCommande) {
        $ligneCommande->setQuantite($ligneCommande->getQuantite() + 1);
        $this->entityManager->persist($ligneCommande);
        $this->entityManager->flush();
    }

    return $this->redirectToRoute('ajouter_produit_panier', ['ref' => $ligneCommande->getProduit()->getRef()]);
}









/**
     * @Route("/panier/diminuer_quantite/{idLigneCommande}", name="diminuer_quantite")
     */
    public function diminuerQuantite($idLigneCommande): Response
    {
        $ligneCommande = $this->ligneCommandeRepository->find($idLigneCommande);
    
        if ($ligneCommande) {
            if ($ligneCommande->getQuantite() > 1) {
            
                $ligneCommande->setQuantite($ligneCommande->getQuantite() - 1);
            } else {
            
            }
            $this->entityManager->flush();
    
            return $this->redirectToRoute('ajouter_produit_panier', ['ref' => $ligneCommande->getProduit()->getRef()]);

        }
    
     
        
    }
    
    
    



















/**
 * @Route("/commande/paiement_livraison/{id}", name="payment", methods={"POST","GET"})
 */
public function modifierEtatCommande(EntityManagerInterface $entityManager, Request $request, $id,MailerInterface $mailer,TwigEnvironment $twig)
{
    $commande = $entityManager->getRepository(Commande::class)->find($id);
    if (!$commande) {
        throw $this->createNotFoundException('La commande n\'existe pas.');
    }

    if ($request->isMethod('POST')) {
        $etatCommande = $request->request->get('etatCommande');
        if ($etatCommande === 'nonLivree') {
            $commande->setEtat('non livrée');
        }
        $emailContent = $twig->render('panier/email.html.twig', [
            'commande' => $commande,
        ]);

        $email = (new Email())
        ->from('yo.yotalent7@gmail.com') 
        ->to('hmachlouche@gmail.com')
        ->subject('Confirmation de votre commande')
        ->html($emailContent);

    $mailer->send($email);





        $entityManager->persist($commande);
        $entityManager->flush();

      
        return $this->redirectToRoute('route', ['id' => $commande->getId()]);
    }

}









#[Route('/commande/valider/{id}', name: 'route', methods: ['GET', 'POST'])]
public function route(EntityManagerInterface $entityManager, Request $request, $id): Response
{
    $commande = $entityManager->getRepository(Commande::class)->find($id);

  
    $tauxDeConversion = 3.3;
   

    $remise = $commande->getRemise(); 
    $prixTotalAvecRemise = $commande->getTotaleCommande() - $remise;

    $totalEnDevise = ($commande->getTotaleCommande() - $remise )/ $tauxDeConversion;


    return $this->render('panier/valider.html.twig', [
        'commande' => $commande,
        'totalEnDevise' => $totalEnDevise,
        'prixTotalAvecRemise' => $prixTotalAvecRemise, 
    ]);
}



    #[Route('/commande/valider/maps/{id}', name: 'routeMaps', methods: ['GET', 'POST'])]
    public function routeMaps(EntityManagerInterface $entityManager, Request $request, $id): Response
    {
        $commande = $entityManager->getRepository(Commande::class)->find($id);
        return $this->render('panier/maps.html.twig', [
            'commande' => $commande,
        ]);
        
        }



     
       
        


 /**
 * @Route("/commande/validermaps/{id}", name="maps_valider", methods={"POST"})
 */
public function validerCommande(EntityManagerInterface $entityManager, Request $request, $id): JsonResponse // Ajout de $id en paramètre
{
    $data = json_decode($request->getContent(), true);

    $latitude = $data['latitude'];
    $longitude = $data['longitude'];
  
    $commande = $entityManager->getRepository(Commande::class)->find($id);

    if (!$commande) {
        return new JsonResponse(['error' => 'La commande n\'existe pas.'], JsonResponse::HTTP_NOT_FOUND);
    }

    $commande->setLatitude((float) $latitude);
    $commande->setLongitude((float) $longitude);

    $entityManager->persist($commande);
    $entityManager->flush();

    return new JsonResponse(['success' => true]);
   
}










    
    



#[Route('/panier', name: 'voir_panier')]
public function voirPanier(Request $request): Response
{
    $session = $request->getSession();
    $panierId = $session->get('panier_id');

    if (!$panierId) {
        // Aucun panier n'a été créé, afficher un panier vide ou un message approprié
    }

    $panier = $this->panierRepository->find($panierId);
    $lignesCommande = $panier->getLignesCommande();
    $nombreArticlesDansPanier = count($panier->getLignesCommande());

    return $this->render('panier/index.html.twig', [
        'lignesCommande' => $lignesCommande,
        'nombreArticlesDansPanier' => $nombreArticlesDansPanier,
        
    ]);
}
// Dans PanierController

public function getNombreArticlesDansPanier(): int
{
    $session = $this->get('session');
    $panierId = $session->get('panier_id');

    if (!$panierId) {
        return 0;
    }

    $panier = $this->panierRepository->find($panierId);
    if (!$panier) {
        return 0;
    }

    return count($panier->getLignesCommande());
}


#[Route('/panier/compteur', name: 'panier_compteur')]
public function compteur(): Response
{
    $session = $this->get('session');
    $panierId = $session->get('panier_id');
    $nombreArticlesDansPanier = 0;

    if ($panierId) {
        $panier = $this->panierRepository->find($panierId);
        if ($panier) {
            $nombreArticlesDansPanier = count($panier->getLignesCommande());
        }
    }

    return $this->render('panier/compteur.html.twig', [
        'nombreArticlesDansPanier' => $nombreArticlesDansPanier,
    ]);
}

    
}