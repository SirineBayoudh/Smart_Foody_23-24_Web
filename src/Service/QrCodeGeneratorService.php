<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use App\Repository\CommandeRepository;


class QrCodeGeneratorService extends AbstractController // Correction du nom de la classe
{
    #[Route('/qr-code/{id}', name: 'app_qr_code')] // Correction de l'annotation de route
    public function generateQrCode(CommandeRepository $commandeRepository, int $id): Response // Correction du nom de la méthode
    {
        $commande = $commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('La commande n\'existe pas.');
        }

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
            ->setSize(300)
            ->setMargin(10);

        $qrCodeImage = $writer->write($qrCode)->getDataUri();

        return $this->render('qr_code_generator/index.html.twig', [
            'qrCodeImage' => $qrCodeImage,
            'commande' => $commande, 
            
        ]);
    }
}
