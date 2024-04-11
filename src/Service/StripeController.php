<?php

namespace App\Service;
use App\Repository\PanierRepository;
use App\Repository\ProduitRepository;
use App\Repository\LigneCommandeRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\CommandeRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Stripe\Stripe;
use Stripe\Charge;
use App\Entity\Commande;
use Doctrine\ORM\EntityManagerInterface;

class StripeController extends AbstractController
{ private $commandeRepository;
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
    /**
     * @Route("/stripe", name="stripe")
     */
    public function index(SessionInterface $session): Response
    {
        $totalEnDevise = $session->get('totalEnDevise', 0);

        return $this->render('stripe/index.html.twig', [
            'stripe_key' => $_ENV["STRIPE_KEY"],
            'totalEnDevise' => $totalEnDevise,
        ]);
    }

    /**
     * @Route("/stripe/create-charge", name="stripe_charge", methods={"POST"})
     */
    public function createCharge(Request $request, SessionInterface $session, EntityManagerInterface $entityManager)
    {
        Stripe::setApiKey($_ENV["STRIPE_SECRET"]);

        $totalEnDevise = $session->get('totalEnDevise', 0); // Assurez-vous d'avoir une valeur par défaut
        $amountInCents = round($totalEnDevise * 100); // Convertir en cents pour Stripe et arrondir

        try {
            $charge = Charge::create([
                "amount" => $amountInCents,
                "currency" => "eur",
                "source" => $request->request->get('stripeToken'),
                "description" => "Payment Test"
            ]);
            
            // Paiement réussi, changer l'état de la commande
            $commandeId = $session->get('idcommande'); // Supposons que vous stockez l'ID de la commande dans la session lors de sa création
            $commande = $entityManager->getRepository(Commande::class)->find($commandeId);

            if ($commande) {
                $commande->setEtat('non livrée'); // Changer l'état de la commande à "non livrée" après paiement
                $entityManager->persist($commande);
                $entityManager->flush();
            }

            // Vider le panier après un paiement réussi
            $this->viderPanier($request);

            $this->addFlash('success', 'Payment Successful!');
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->addFlash('error', 'Payment Error: ' . $e->getMessage());
        }

        return $this->redirectToRoute('stripe', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Vider le panier en rendant les lignes de commande orphelines.
     */
    private function viderPanier(Request $request): void
    {
        $session = $request->getSession();
        $panierId = $session->get('panier_id');
        if ($panierId) {
            $panier = $this->panierRepository->find($panierId);
            foreach ($panier->getLignesCommande() as $ligne) {
                // Rendre la ligne de commande orpheline
                $ligne->setPanier(null);
                $this->entityManager->persist($ligne);
            }
            // Réinitialiser le total du panier à 0
            $panier->setTotale(0.0);
            $this->entityManager->flush();
        }
    }
}
