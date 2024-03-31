<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CommandeRepository;
use Dompdf\Dompdf;
use Dompdf\Options;



class CommandeController extends AbstractController
{



    #[Route('/admin', name: 'commande_stat')]
    public function afficherStatistiques(CommandeRepository $commandeRepository): Response
    {
        $clientsFideles = $commandeRepository->trouverClientsFideles();
        
        $commandes = $commandeRepository->findByEtat('Non livrée');

    
        return $this->render('commande/index.html.twig', [
          'clientsFideles' => $clientsFideles,
            'commandes' => $commandes, 
        ]);
    }
    





/*#[Route('/commande/pdf', name: 'commande_pdf_all')]
public function pdfAll(CommandeRepository $commandeRepository): Response
{
    $commandes = $commandeRepository->findAll();

   
   
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);


        $html = $this->renderView('commande/all_pdf.html.twig', [
            'commandes' => $commandes,
        ]);


        $dompdf->loadHtml($html);
        $dompdf->render();

        $output = $dompdf->output();

        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="ListeCommande.pdf"');

        return $response;

    
}*/



/*#[Route('/commande/pdf/{id}', name: 'commande_pdf')]
public function pdf(CommandeRepository $commandeRepository, int $id): Response
{
        $commande = $commandeRepository->find($id);
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);


        $html = $this->renderView('commande/detail_pdf.html.twig', [
            'commande' => $commande,
        ]);


        $dompdf->loadHtml($html);
        $dompdf->render();

        $output = $dompdf->output();

        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="DetailsCommande.pdf"');

        return $response;

    
}*/


    #[Route('/commandes', name: 'app_commandes')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findByEtat('Non livrée');

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }




    #[Route('/detail/{id}', name: 'commande_detail')]
    public function detail(CommandeRepository $commandeRepository, int $id): Response
    {
        $commande = $commandeRepository->find($id);
    
        if (!$commande) {
            throw $this->createNotFoundException('La commande n\'existe pas.');
        }
    
        return $this->render('commande/detail.html.twig', [
            'commande' => $commande,
            
        ]);
    }
    




  



    #[Route('/livre', name: 'commandes_livre')]
    public function livre(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findByEtat('livré');
        $clientsFideles = $commandeRepository->trouverClientsFideles();

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
            'clientsFideles' => $clientsFideles,
        ]);
    }

    #[Route('/nonlivre', name: 'commandes_non_livre')]
    public function nonLivre(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findByEtat('Non livrée');
        $clientsFideles = $commandeRepository->trouverClientsFideles();

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
            'clientsFideles' => $clientsFideles,
        ]);
    }


    #[Route('/encore', name: 'commandes_en_cours')]
    public function encore(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findByEtat('en cours');
        $clientsFideles = $commandeRepository->trouverClientsFideles();

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
            'clientsFideles' => $clientsFideles,
        ]);
    }

}
