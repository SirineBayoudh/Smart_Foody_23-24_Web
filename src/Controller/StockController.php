<?php

namespace App\Controller;

use App\Entity\Alerte;
use App\Entity\Produit;
use App\Entity\Stock;
use App\Form\AjouterStockType;
use App\Repository\ProduitRepository;
use App\Repository\StockRepository;
use App\Service\SmsGenerator;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StockController extends AbstractController
{
    // #[Route('/stock', name: 'stock_get')]
    // public function getStock(StockRepository $stockRepo, ProduitRepository $produitRepo, EntityManagerInterface $entityManager, FlashBagInterface $flashBag): Response
    // {
    //     // Récupérez les données de la table Stock et Produit
    //     $stocks = $stockRepo->findAll();
    //     $produits = $produitRepo->findAll();

    //     // Parcourez les stocks pour effectuer la vérification et afficher une alerte si nécessaire
    //     foreach ($stocks as $stock) {
    //         // Vérifiez si nbVendu est nul
    //         if ($stock->getNbvendu() === null) {
    //             // Affectez la valeur 0 à nbVendu
    //             $stock->setNbvendu(0);
    //         }

    //         // Vérifiez si nbVendu est égal à quantite
    //         if ($stock->getNbvendu() === $stock->getQuantite()) {
    //             // Ajoutez un message flash avec une classe pour l'alerte rouge
    //             $flashBag->add('danger', 'La quantité vendue est égale à la quantité en stock pour le produit ' . $stock->getNom());
    //         }
    //     }

    //     // Parcourez les stocks pour calculer et mettre à jour le coût pour chaque stock
    //     foreach ($stocks as $stock) {
    //         // Récupérez la marque et la quantité du stock actuel
    //         $stockMarque = $stock->getMarque();
    //         $quantite = $stock->getQuantite();

    //         // Recherchez le produit correspondant dans la table Produit
    //         foreach ($produits as $produit) {
    //             // Si la marque du stock correspond à la marque du produit
    //             if ($produit->getMarque() === $stockMarque) {
    //                 // Récupérez le prix du produit
    //                 $prix = $produit->getPrix();

    //                 // Calculez le coût en multipliant la quantité par le prix
    //                 $cout = $quantite * $prix;

    //                 // Mettez à jour le coût dans l'entité Stock
    //                 $stock->setCout($cout);

    //                 // Enregistrez les modifications dans la base de données
    //                 $entityManager->flush();

    //                 // Sortez de la boucle car nous avons trouvé le produit correspondant
    //                 break;
    //             }
    //         }
    //     }

    //     // Passez les stocks à la vue
    //     return $this->render('stock/index.html.twig', [
    //         'stocks' => $stocks,
    //     ]);
    // }

    #[Route('/stock', name: 'stock_get')]
    public function getStock(
        StockRepository $stockRepo,
        ProduitRepository $produitRepo,
        EntityManagerInterface $entityManager,
        FlashBagInterface $flashBag,
        SmsGenerator $smsGenerator,
        Request $request
    ): Response {

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


        // Flush toutes les entités persistées
        $entityManager->flush();

        // Passez les stocks à la vue
        return $this->render('stock/index.html.twig', [
            'stocks' => $stocks,
            'futureStocks' => $futureStocks,
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
        $form = $this->createFormBuilder($j)
            ->add('nom', TextType::class, ['disabled' => true]) // Rend le champ 'nom' non éditable
            // ->add('ref_produit', TextType::class, ['disabled' => true]) // Rend le champ 'ref_produit' non éditable
            ->add('marque', TextType::class, ['disabled' => true]) // Rend le champ 'marque' non éditable
            ->add('quantite') // Champ quantite reste éditable
            ->add('date_arrivage') // Champ quantite reste éditable

            ->getForm();
        $form->handleRequest($req);
        $em = $manager->getManager();
        if ($form->isSubmitted()  && $form->isValid()) {

            $em->persist($j);  // juste préparer les requetes
            $em->flush();

            return $this->redirectToRoute("stock_get");
        }
        return  $this->renderForm(
            'stock/ajouter.html.twig',
            [
                'form' => $form,
                //'stocks' => $repo->findAll() // Passer les stocks à la vue
            ]
        );
    }



    #[Route('/ajouter/stock', name: 'app_ajouter_stock')]

    public function index(Request $request, ManagerRegistry $manager, ProduitRepository $produitRepository): Response
    {
        $stock = new Stock();
        $form = $this->createForm(AjouterStockType::class, $stock);
        $form->handleRequest($request);
        $em = $manager->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
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
        }

        // Afficher le formulaire si celui-ci n'est pas soumis ou n'est pas valide
        return $this->render('stock/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/stock_venir', name: 'stock_venir')]
    public function getFutureStocks(StockRepository $stockRepository): Response
    {
        // Récupérer les stocks à venir depuis le repository
        $futureStocks = $stockRepository->findFutureStocks();
        foreach ($futureStocks as $stock) {
            // Vérifiez si nbVendu est nul
            if ($stock->getNbvendu() === null) {
                // Affectez la valeur 0 à nbVendu
                $stock->setNbvendu(0);
            }
        }
        return $this->render('stock/future_stocks.html.twig', [
            'futureStocks' => $futureStocks,
        ]);
    }

    // #[Route('/afficher-calendrier', name: 'afficher_calendrier')]
    // public function afficherCalendrier(StockRepository $stockRepo,  Request $request): Response
    // {
    //     $futureStocks = $stockRepo->findFutureStocks();

    //     // Utilisez la méthode findExistantStocks avec ou sans terme de recherche
    //     $searchTerm = $request->query->get('search');

    //     // Utilisez la méthode findExistantStocks avec ou sans terme de recherche
    //     $stocks = $stockRepo->findExistantStocks($searchTerm);
    //     return $this->render('stock/calendar.html.twig', [
    //         'stocks' => $stocks,
    //         'futureStocks' => $futureStocks,
    //     ]);
    // }


    #[Route('/scatter-chart', name: 'scatter_chart')]
    public function scatterChart(StockRepository $stockRepository): Response
    {
        // Récupérer les données depuis la base de données
        $stocks = $stockRepository->findAll();

        // Formater les données pour Twig
        $dataForTwig = [
            'stocks' => $stocks
        ];

        // Rendre le template avec les données
        return $this->render('stock/index.html.twig', $dataForTwig);
    }
}
