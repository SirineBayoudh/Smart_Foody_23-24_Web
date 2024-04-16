<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CommandeRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;

use App\Form\SearchType;





class CommandeController extends AbstractController
{



   /* #[Route('/admin', name: 'commande_stat')]
    public function afficherStatistiques(CommandeRepository $commandeRepository): Response
    {
        $clientsFideles = $commandeRepository->trouverClientsFideles();
        
        $commandes = $commandeRepository->findByEtat('Non livrée');

    
        return $this->render('commande/index.html.twig', [
          'clientsFideles' => $clientsFideles,
            'commandes' => $commandes, 
            
        ]);
    }*/
    
    /*#[Route('/stat', name: 'statistiques')]
    public function afficherStatistique(CommandeRepository $commandeRepository): Response
    {
        $clientsFideles = $commandeRepository->trouverClientsFideles();
        
        $commandes = $commandeRepository->findByEtat('Non livrée');

    
        return $this->render('commande/statistiques.html.twig', [
          'clientsFideles' => $clientsFideles,
            'commandes' => $commandes, 
        ]);
    }*/
    #[Route('/statistiques', name: 'statistiques')]
    public function afficherStatistique(CommandeRepository $commandeRepository,Request $request,PaginatorInterface $paginator): Response
    { $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
        $searchQuery = $form->get('search')->getData() ?: $request->query->get('q', '');
    
        $commandesQuery = $commandeRepository->findCommandesByQuery($searchQuery);
        $clientsFideles = $commandeRepository->trouverClientsFideles();
        $chartType = 'mixed'; // Définir le type de graphique ici
        $pagination = $paginator->paginate(
            $commandesQuery,
            $request->query->getInt('page', 1),
            3
        );
       
        return $this->render('commande/statistiques.html.twig', [
            'clientsFideles' => $clientsFideles,
            
            'chartType' => $chartType,
            'search_form' => $form->createView(),
            'pagination'=>$pagination,
            
        ]);
    }

    
    

    #[Route('/commande/changer-etat/{id}', name: 'changer_etat_commande', methods: ['POST'])]
    public function changerEtatCommande(EntityManagerInterface $entityManager, int $id,CommandeRepository $commandeRepository): Response
    {
        $commande = $commandeRepository->find($id);
    
        if (!$commande) {
            throw $this->createNotFoundException('La commande n\'existe pas.');
        }

        // Déterminez le nouvel état basé sur l'état actuel
        switch ($commande->getEtat()) {
            case 'non livrée':
                $nouvelEtat = 'en cours';
                break;
            case 'en cours':
                $nouvelEtat = 'livré';
                break;
            case 'livré':
                // Définir le nouvel état si nécessaire, ou laisser inchangé
                $nouvelEtat = 'non livrée'; // ou 'livré' si vous ne voulez pas changer l'état
                break;
            default:
                $nouvelEtat = 'non livrée'; // ou gérer autrement
                break;
        }

        $commande->setEtat($nouvelEtat);
        $entityManager->flush();

        $this->addFlash('success', 'L\'état de la commande a été mis à jour.');

        return $this->redirectToRoute('commande_detail', ['id' => $commande->getId()]);
    }
    //recherche 
    #[Route('/commandes/search', name: 'app_commandes_search')]
    public function search(Request $request, PaginatorInterface $paginator, CommandeRepository $commandeRepository): Response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && !$form->isValid()) {
            // Vous pouvez gérer des logiques supplémentaires ici, comme enregistrer des logs ou des statistiques
        }
    
        $searchQuery = $form->get('search')->getData() ?: $request->query->get('q', '');
    
        $commandesQuery = $commandeRepository->findCommandesByQuery($searchQuery);
    
        $pagination = $paginator->paginate(
            $commandesQuery,
            $request->query->getInt('page', 1),
            10
        );
    
        return $this->render('commande/index.html.twig', [
            'search_form' => $form->createView(),
            'pagination' => $pagination,
        ]);
    }
    
    #[Route('/autocomplete', name: 'autocomplete_search')]
    public function autocomplete(Request $request, CommandeRepository $commandeRepository): JsonResponse
    {
        $query = $request->query->get('query', '');
        $results = $commandeRepository->findSuggestionsForQuery($query);
        
        return $this->json($results);
    }
    
    

    #[Route('/commandes', name: 'app_commandes')]
    public function index(CommandeRepository $commandeRepository, PaginatorInterface $paginator, Request $request): Response
    {
        // Créer le formulaire de recherche
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
    
        // Gérer la requête de recherche
        $searchQuery = $form->get('search')->getData() ?: $request->query->get('q', '');
    
        // Récupérer les commandes basées sur la requête de recherche
        $commandesQuery = $commandeRepository->findCommandesByQuery($searchQuery);
        
        // Récupérer les commandes pour chaque état
        $commandesEnCours = $commandeRepository->findByEtat('en cours');
        $commandesNonLivre = $commandeRepository->findByEtat('non livrée');
        $commandesLivre = $commandeRepository->findByEtat('livré');
        
        // Paginer les commandes
        $pagination = $paginator->paginate(
            
            
            array_merge($commandesEnCours, $commandesNonLivre, $commandesLivre), // Liste concaténée de toutes les commandes
            $request->query->getInt('page', 1), // Page par défaut
            3 // Limite par page
        );
       /* $pagination = $paginator->paginate(
    
            $commandesQuery,
        );*/
        
        return $this->render('commande/index.html.twig', [
            'pagination' => $pagination,
       
            'search_form' => $form->createView(),
            'commandesQuery'=>$commandesQuery,
        ]);
    }
    

    public function compteur(CommandeRepository $commandeRepository, string $type): Response
    {
        $nombre = 0;
        switch ($type) {
            case 'livré':
                $nombre = count($commandeRepository->findBy(['etat' => 'livré']));
                break;
            case 'non livrée':
                $nombre = count($commandeRepository->findBy(['etat' => 'non livrée']));
                break;
            case 'en cours':
                $nombre = count($commandeRepository->findBy(['etat' => 'en cours']));
                break;
        }

        return $this->render('commande/compteur_commandes.html.twig', [
            'type' => $type,
            'nombreLivre' => $nombre,
            'nombreNonLivre' => $nombre,
            'nombreEnCours' => $nombre,
        ]);
    }


#[Route('/commande/pdf', name: 'commande_pdf_all')]
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

    
}



#[Route('/commande/pdf/{id}', name: 'commande_pdf')]
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

    return $this->render('commande/details_commande.html.twig', [
        'commande' => $commande,
        'lignesCommande' => $lignesCommande, // Assurez-vous que cette variable est bien passée au template
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
    public function livre(CommandeRepository $commandeRepository,PaginatorInterface $paginator, Request $request): Response
    {  $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
    
        // Gérer la requête de recherche
        $searchQuery = $form->get('search')->getData() ?: $request->query->get('q', '');
        $commandes = $commandeRepository->findByEtat('livré');
        $clientsFideles = $commandeRepository->trouverClientsFideles();
     
        $pagination = $paginator->paginate(
            
            $commandes, // Query à paginer
            $request->query->getInt('page', 1), // Numéro de page par défaut
            3// Nombre d'éléments par page
        );

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
            'pagination' => $pagination,
            'search_form' => $form->createView(),
           
            
            'clientsFideles' => $clientsFideles,
        ]);
    }

    #[Route('/nonlivre', name: 'commandes_non_livre')]
    public function nonLivre(CommandeRepository $commandeRepository,PaginatorInterface $paginator, Request $request): Response
    { $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
    
        // Gérer la requête de recherche
        $searchQuery = $form->get('search')->getData() ?: $request->query->get('q', '');
        $commandes = $commandeRepository->findByEtat('Non livrée');
        $clientsFideles = $commandeRepository->trouverClientsFideles();
        $pagination = $paginator->paginate(
            $commandes, // Query à paginer
            $request->query->getInt('page', 1), // Numéro de page par défaut
            3 // Nombre d'éléments par page
        );

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
            'clientsFideles' => $clientsFideles,
            'pagination' => $pagination,
            'search_form' => $form->createView(),
        ]);
    }


    #[Route('/encore', name: 'commandes_en_cours')]
    public function encore(CommandeRepository $commandeRepository,PaginatorInterface $paginator, Request $request): Response
    { $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);
    
        // Gérer la requête de recherche
        $searchQuery = $form->get('search')->getData() ?: $request->query->get('q', '');
        $commandes = $commandeRepository->findByEtat('en cours');
        $clientsFideles = $commandeRepository->trouverClientsFideles();
        $pagination = $paginator->paginate(
            $commandes, // Query à paginer
            $request->query->getInt('page', 1), // Numéro de page par défaut
            3 // Nombre d'éléments par page
        );
        dump($pagination);
     

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
            'clientsFideles' => $clientsFideles,
            'pagination' => $pagination,
            'search_form' => $form->createView(),
         
        ]);
    }
   

}
