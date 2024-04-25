<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Commande;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class ProfilController extends AbstractController
{
    #[Route('/profil/{id}', name: 'app_profil')]
    public function index(int $id, CommandeRepository $commandeRepository): Response
    {
        $id_client = 14;
       

        
        $commande = $commandeRepository->find($id);
        $remise = $commande->getRemise(); 
        $prixTotalAvecRemise = $commande->getTotaleCommande() - $remise;

        // Récupérer l'historique des commandes du client avec l'ID passé en paramètre
        $historiqueCommandes = $commandeRepository->findHistoriqueCommandesByClientId($id_client);

        return $this->render('profil/index.html.twig', [
            'historiqueCommandes' => $historiqueCommandes,
            'commande'=>$commande,
            'prixTotalAvecRemise' => $prixTotalAvecRemise,  // Passer les commandes au template
        ]);
    }

    /**
     * @Route("/commande/{id}", name="commande_details")
     */
    public function commandeDetails($id, CommandeRepository $commandeRepository)
    {
        $commande = $commandeRepository->find($id);
        $remise = $commande->getRemise(); 
        $prixTotalAvecRemise = $commande->getTotaleCommande() - $remise;


        if (!$commande) {
            throw $this->createNotFoundException('La commande demandée n\'existe pas');
        }

        // Génération du QR code
        $qrCode = $this->generateQrCode($commande);

        // Passer les données nécessaires au template Twig
        return $this->render('profil/details_commande.html.twig', [
            'commande' => $commande,
            'qrCode' => $qrCode,
            'prixTotalAvecRemise' => $prixTotalAvecRemise, // Passer le QR code au template
        ]);
    }

    /**
     * @Route("/qr-code/{id}", name="app_qr_code")
     */
    public function generateQrCode(Commande $commande): string
    {
        $writer = new PngWriter();

        // Construction du contenu du QR Code
        $content = "Commande ID: {$commande->getId()}\n";
        $content .= "Date: " . $commande->getDateCommande()->format('Y-m-d') . "\n";
        $content .= "Total: " . $commande->getTotaleCommande() . " TND\n";
        foreach ($commande->getLignesCommande() as $ligne) {
            $content .= "{$ligne->getProduit()->getMarque()}: ";
            $content .= "{$ligne->getQuantite()} x ";
            $content .= "{$ligne->getProduit()->getPrix()} TND\n";
        }

        $qrCode = QrCode::create($content)
            ->setSize(150)
            ->setMargin(10);

        $qrCodeImage = $writer->write($qrCode)->getDataUri();

        return $qrCodeImage;
    }
}
