<?php

namespace App\Controller;

use App\Entity\Alerte;
use App\Entity\LigneCommande;
use App\Entity\Produit;
use App\Entity\Stock;
use App\Form\AjouterStockType;
use App\Repository\ProduitRepository;
use App\Repository\StockRepository;
use App\Service\FacebookService;
use App\Service\SmsGenerator;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use PHPExcel;
use PHPExcel_IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Stichoza\GoogleTranslate\GoogleTranslate;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\JsonResponse;

class StockController extends AbstractController
{
    #[Route('/tr', name: 'translate_to_english')]
    public function translateToEnglish(Request $request): Response
    {
        // Récupérer le contenu à traduire depuis la requête
        $contentToTranslate = $request->request->get('content');

        // Créer une instance de GoogleTranslate
        $translator = new GoogleTranslate();

        // Traduire le contenu de français en anglais
        $translatedContent = $translator->setSource('fr')->setTarget('en')->translate($contentToTranslate);

        // Retourner une réponse avec le contenu traduit
        return new Response($translatedContent);
    }
    #[Route('/translate-to-french', name: 'translate_to_french')]
    public function translateToFrench(Request $request): Response
    {
        // Récupérer le contenu à traduire depuis la requête
        $contentToTranslate = $request->request->get('content');

        // Créer une instance de GoogleTranslate
        $translator = new GoogleTranslate();

        // Traduire le contenu de l'anglais en français
        $translatedContent = $translator->setSource('en')->setTarget('fr')->translate($contentToTranslate);

        // Retourner une réponse avec le contenu traduit
        return new Response($translatedContent);
    }
    #[Route('/stock', name: 'stock_get')]
    public function getStock(
        StockRepository $stockRepo,
        ProduitRepository $produitRepo,
        EntityManagerInterface $entityManager,
        FlashBagInterface $flashBag,
        SmsGenerator $smsGenerator,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $this->updateStockFromCommande($entityManager);
        $queryBuilder = $stockRepo->createQueryBuilder('s');

        $pagination = $paginator->paginate(
            $queryBuilder->where('s.date_arrivage <= :date')
                ->setParameter('date', new \DateTime())
                ->getQuery(),
            $request->query->getInt('page', 1),
            5
        );
        $futureStocks = $stockRepo->findFutureStocks();

        // Utilisez la méthode findExistantStocks avec ou sans terme de recherche
        $searchTerm = $request->query->get('search');

        // Utilisez la méthode findExistantStocks avec ou sans terme de recherche
        $stocks = $stockRepo->findExistantStocks($searchTerm);
        $produits = $produitRepo->findAll();

        $alertMessages = []; // Tableau pour stocker les alertes temporaires

        // Parcourez les stocks pour effectuer la vérification et créer une alerte si nécessaire
        foreach ($stocks as $stock) {
            // Vérifiez si nbVendu est nul
            if ($stock->getNbvendu() === null) {
                // Affectez la valeur 0 à nbVendu
                $stock->setNbvendu(0);
            }

            // Vérifiez si nbVendu est égal à quantite - 5
            if ($stock->getNbvendu() === $stock->getQuantite() - 5) {
                // Créez une nouvelle instance d'Alerte
                $alerte = new Alerte();
                $alerte->setDescription_alerte('Le stock ' . $stock->getNom() . ' est bientôt en rupture.');
                $alerte->setDateAlerte(new \DateTime()); // Date actuelle
                $alerte->setType(false); // Mettez le type d'alerte à faux

                // Récupérez l'objet Stock correspondant à partir de l'ID
                $alerteStock = $stockRepo->find($stock->getId_s());
                if ($alerteStock) {
                    // Associez le stock à l'alerte
                    $alerte->setId_Stock($alerteStock);
                    // Persistez l'entité Alerte
                    $entityManager->persist($alerte);
                    // Ajoutez la description de l'alerte au tableau des messages
                    $alertMessages[] = $alerte->getDescription_alerte();
                }
            }
            // Vérifiez si nbVendu est égal à quantite
            if ($stock->getNbvendu() === $stock->getQuantite()) {
                $phoneNumber = '+21627674746'; // Remplacez ceci par le numéro de téléphone approprié
                $name = 'Nom'; // Remplacez ceci par le nom approprié
                $message = 'Le stock ' . $stock->getNom() . ' est en rupture.'; // Message à envoyer

                // Envoi du SMS en utilisant le service SmsGenerator
                $smsGenerator->SendSms($phoneNumber, $name, $message);
            }
        }

        // Ajoutez les alertes au FlashBag
        foreach ($alertMessages as $message) {
            $flashBag->add('danger', $message);
        }

        // Parcourez les stocks pour calculer et mettre à jour le coût pour chaque stock
        $this->calculerCoutStocks($stocks, $produits, $entityManager);

        $totalStock = 0;
        foreach ($stocks as $stock) {
            $totalStock++; // Incrémentez la variable $totalStock à chaque itération
        }

        // Flush toutes les entités persistées
        $entityManager->flush();

        // Passez les stocks à la vue
        return $this->render('stock/index.html.twig', [
            'pagination' => $pagination,
            'stocks' => $stocks,
            'futureStocks' => $futureStocks,
            'totalStock' => $totalStock,
        ]);
    }

    private function calculerCoutStocks(array $stocks, array $produits, EntityManagerInterface $entityManager): void
    {
        foreach ($stocks as $stock) {
            $stockMarque = $stock->getMarque();
            $quantite = $stock->getQuantite();

            foreach ($produits as $produit) {
                if ($produit->getMarque() === $stockMarque) {
                    $prix = $produit->getPrix();
                    $cout = $quantite * $prix;
                    $stock->setCout($cout);
                    $entityManager->flush();
                    break;
                }
            }
        }
    }

    #[Route('/deleteStock/{id}', name: 'stock_delete')]
    public function deleteStock(ManagerRegistry $manager, $id, StockRepository $repo): Response
    {
        $stock = $repo->find($id);
        $em = $manager->getManager();
        $em->remove($stock);
        $em->flush();
        return $this->redirectToRoute("stock_get");
    }

    #[Route('/edit_stock/{id}', name: 'stock_edit')]
    public function editstock(Request $req, ManagerRegistry $manager, $id, StockRepository $repo): Response
    {
        $j = $repo->find($id);
        $form = $this->createForm(AjouterStockType::class, $j);
        // ->add('nom', TextType::class, ['disabled' => true]) // Rend le champ 'nom' non éditable
        // // ->add('ref_produit', TextType::class, ['disabled' => true]) // Rend le champ 'ref_produit' non éditable
        // ->add('marque', TextType::class, ['disabled' => true]) // Rend le champ 'marque' non éditable
        // ->add('quantite') // Champ quantite reste éditable
        // ->add('date_arrivage') // Champ quantite reste éditable

        // ->getForm();
        $form->handleRequest($req);
        $em = $manager->getManager();
        $emptySubmission = false;
        if ($form->isSubmitted()  && $form->isValid()) {
            $emptySubmission = true;
            $em->persist($j);  // juste préparer les requetes
            $em->flush();

            return $this->redirectToRoute("stock_get");
        }
        return  $this->renderForm(
            'stock/ajouter.html.twig',
            [
                'form' => $form,
                'emptySubmission' => $emptySubmission ?? false,
                //'stocks' => $repo->findAll() // Passer les stocks à la vue
            ]
        );
    }



    #[Route('/ajouter/stock', name: 'app_ajouter_stock')]

    public function index(Request $request, ManagerRegistry $manager, StockRepository $stockRepository, ProduitRepository $produitRepository): Response
    {
        $stock = new Stock();
        $form = $this->createForm(AjouterStockType::class, $stock);
        $form->handleRequest($request);
        $em = $manager->getManager();
        $emptySubmission = false;
        if ($form->isSubmitted() && $form->isValid()) {
            $emptySubmission = true;
            $marque = $stock->getMarque();

            $existingStock = $stockRepository->findOneBy(['marque' => $marque]);

            if ($existingStock !== null) {
                // Add a flash message to display the alert
                $this->addFlash('danger', 'La marque existe déjà dans le tableau.');
                return $this->redirectToRoute('stock_get'); // Redirect to the desired page
            }


            // Vérifier si des fichiers ont été téléchargés
            if ($request->files->has('img')) {
                $imageFile = $request->files->get('img')[0];
                if ($imageFile) {
                    // Récupérer le nom du fichier de l'image
                    $image = $imageFile->getClientOriginalName();

                    // Enregistrer le nom du fichier dans l'entité Stock
                    $stock->setImage($image);
                }
                $marque = $stock->getMarque();

                // Rechercher l'objet Produit correspondant à la marque sélectionnée
                $produit = $produitRepository->findOneBy(['marque' => $marque]);

                // Vérifier si un produit correspondant a été trouvé
                if ($produit instanceof Produit) {
                    // Récupérer la référence exacte du produit
                    $stock->setRefProduit($produit);
                }
            }

            // Persister l'objet Stock dans la base de données
            $em->persist($stock);
            $em->flush();

            // Rediriger l'utilisateur vers la page de liste des stocks ou une autre page appropriée
            return $this->redirectToRoute('stock_get');
        } elseif ($form->isSubmitted()) {
            $emptySubmission = true;
        }
        // Afficher le formulaire si celui-ci n'est pas soumis ou n'est pas valide
        return $this->render('stock/ajouter.html.twig', [
            'form' => $form->createView(),
            'emptySubmission' => $emptySubmission ?? false,
        ]);
    }



    #[Route('/stock_venir', name: 'stock_venir')]
    public function getFutureStocks(
        StockRepository $stockRepository,
        PaginatorInterface $paginator,
        Request $request,
        ProduitRepository $produitRepo,
        EntityManagerInterface $entityManager
    ): Response {
        // Récupérer les stocks à venir depuis le repository
        $futureStocks = $stockRepository->findFutureStocks();
        $produits = $produitRepo->findAll();

        $queryBuilder = $stockRepository->createQueryBuilder('s');

        $pagination = $paginator->paginate(
            $queryBuilder->where('s.date_arrivage > :date')
                ->setParameter('date', new \DateTime())
                ->getQuery(),
            $request->query->getInt('page', 1),
            5
        );
        $totalStock = 0;
        foreach ($futureStocks as $stock) {
            $totalStock++; // Incrémentez la variable $totalStock à chaque itération
        }

        foreach ($futureStocks as $stock) {
            // Vérifiez si nbVendu est nul
            if ($stock->getNbvendu() === null) {
                // Affectez la valeur 0 à nbVendu
                $stock->setNbvendu(0);
            }
        }
        $this->calculerCoutStocks($futureStocks, $produits, $entityManager);

        return $this->render('stock/future_stocks.html.twig', [
            'futureStocks' => $futureStocks,
            'pagination' => $pagination,
            'totalStock' => $totalStock,
        ]);
    }


    #[Route('/update-stock-from-commande', name: 'update_stock_from_commande')]
    public function updateStockFromCommande(EntityManagerInterface $entityManager): Response
    {
        // Récupérer toutes les lignes de commande
        $ligneCommandes = $entityManager->getRepository(LigneCommande::class)->findAll();

        // Créer un tableau pour stocker les quantités vendues par référence de produit
        $quantitesParRefProduit = [];

        // Parcourir chaque ligne de commande
        foreach ($ligneCommandes as $ligneCommande) {
            // Récupérer la référence du produit de la ligne de commande
            $refProduit = $ligneCommande->getRefProduit();

            // Récupérer la quantité de la ligne de commande
            $quantite = $ligneCommande->getQuantite();

            // Si la référence de produit existe déjà dans le tableau, ajouter la quantité
            if (array_key_exists($refProduit->getRef(), $quantitesParRefProduit)) {
                $quantitesParRefProduit[$refProduit->getRef()] += $quantite;
            } else { // Sinon, initialiser la quantité
                $quantitesParRefProduit[$refProduit->getRef()] = $quantite;
            }
        }

        // Mettre à jour les stocks avec les quantités vendues calculées
        foreach ($quantitesParRefProduit as $refProduitId => $quantite) {
            // Récupérer le stock correspondant à la référence du produit
            $stock = $entityManager->getRepository(Stock::class)->findOneBy(['ref_produit' => $refProduitId]);

            // Si un stock est trouvé, mettre à jour la quantité vendue
            if ($stock) {
                $stock->setNbVendu($quantite);
                $entityManager->persist($stock);
            }
        }

        // Enregistrer les modifications
        $entityManager->flush();

        // Redirection vers une autre route après la mise à jour des stocks
        return $this->redirectToRoute('stock_get');
    }

    #[Route('/pdf', name: 'export_pdf')]
    public function exportPdf(StockRepository $stockRepo): Response
    {
        // Récupérer les données à exporter depuis la base de données
        // $data = $this->getDoctrine()->getRepository(Stock::class)->findExistantStocks();
        $data = $stockRepo->findExistantStocks();
        // Créer une instance de Dompdf avec des options
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        // Générer le contenu HTML pour le PDF à partir des données
        $html = $this->renderView('stock/pdf_template.html.twig', ['data' => $data]);

        // Charger le contenu HTML dans Dompdf
        $dompdf->loadHtml($html);

        // Générer le PDF
        $dompdf->render();

        // Renvoyer le PDF en tant que réponse HTTP
        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.pdf"');

        return $response;
    }
    #[Route('/pdf_venir', name: 'export_pdffuture')]
    public function exportPdf_venir(StockRepository $stockRepo): Response
    {
        // Récupérer les données à exporter depuis la base de données
        // $data = $this->getDoctrine()->getRepository(Stock::class)->findExistantStocks();
        $data = $stockRepo->findFutureStocks();
        // Créer une instance de Dompdf avec des options
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);

        // Générer le contenu HTML pour le PDF à partir des données
        $html = $this->renderView('stock/pdf_template.html.twig', ['data' => $data]);

        // Charger le contenu HTML dans Dompdf
        $dompdf->loadHtml($html);

        // Générer le PDF
        $dompdf->render();

        // Renvoyer le PDF en tant que réponse HTTP
        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.pdf"');

        return $response;
    }

    #[Route('/stocks/search', name: 'app_stocks_search')]
    public function search(StockRepository $stockRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $searchQuery = $request->query->get('q');

        if ($searchQuery) {
            // Effectuer la recherche avec le terme spécifié
            $queryBuilder = $stockRepository->createQueryBuilder('s');
            $queryBuilder->where('s.nom LIKE :searchQuery')
                ->setParameter('searchQuery', '%' . $searchQuery . '%');

            $pagination = $paginator->paginate(
                $queryBuilder->getQuery(),
                $request->query->getInt('page', 1),
                2
            );

            // Récupérer les stocks paginés
            $stocks = $pagination;
        } else {
            // Si aucune requête de recherche n'est spécifiée, récupérer tous les stocks
            $pagination = $paginator->paginate(
                $stockRepository->findAll(),
                $request->query->getInt('page', 1),
                2
            );

            // Récupérer les stocks paginés
            $stocks = $pagination;
        }

        return $this->render('stock/index.html.twig', [
            'stocks' => $stocks,
            'searchQuery' => $searchQuery,
            'pagination' => $pagination,
        ]);
    }

    #[Route('/stat_stock', name: 'statistiques')]
    public function afficherStatistique(StockRepository $stockRepository): Response
    {
        // Récupérer les stocks depuis le repository
        $stocks = $stockRepository->findAll(); // Par exemple, récupérer tous les stocks

        return $this->render('stock/statistiques.html.twig', [
            'stocks' => $stocks, // Transmettre les stocks au modèle Twig
        ]);
    }

    #[Route('/get-categorie', name: 'get_categorie', methods: ['GET'])]
    public function getCategorie(Request $request, ProduitRepository $produitRepository): JsonResponse
    {
        $selectedRef = $request->query->get('ref');
        $categorie = $produitRepository->findCategorieByRef($selectedRef);
        return new JsonResponse(['categorie' => $categorie]);
    }
    #[Route('/get-marques-by-categorie', name: 'get_marques_by_categorie', methods: ["GET"])]
    public function getMarquesByCategorie(Request $request, ProduitRepository $pr): JsonResponse
    {
        // Récupérer la catégorie envoyée depuis la requête AJAX
        $selectedCategorie = $request->query->get('categorie');

        // Récupérer les marques associées à la catégorie
        $marques = $pr->findAllDistinctMarquesByCategorie($selectedCategorie);

        // Retourner les marques au format JSON
        return new JsonResponse(['marques' => $marques]);
    }
}
