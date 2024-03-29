<?php

namespace App\Controller;

use App\Entity\Objectif;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductDashController extends AbstractController
{
    #[Route('/product/dash', name: 'app_product_dash')]
    public function index(): Response
    {
        return $this->render('product_dash/index.html.twig', [
            'controller_name' => 'ProductDashController',
        ]);
    }

    #[Route('/product/all', name: 'product_all')]
    public function listProduit(ProduitRepository $prodrepository): Response
{
    return $this->render('product_dash/list_produit.html.twig', [
        'prod' => $prodrepository->findAll(),
    ]);
}

#[Route('/addproduct', name: 'add_product')]
public function addProduct(ManagerRegistry $manager, Request $request): Response
{
    // Tableau associatif pour stocker les numéros spécifiques des critères
    $criteresNumerotes = [
        'Protein' => 1,
        'Sans_lactose' => 2,
        'Sans_gluten' => 3,
        'Sans_glucose' => 4,
        'Sans_sels' => 5,
        // Ajoutez d'autres critères ici avec leur numéro spécifique
    ];

    // Récupérer le référentiel (repository) des critères
    $criteriaRepository = $this->getDoctrine()->getRepository(Objectif::class);

    // Récupérer la liste des critères depuis la base de données
    $criteres = $criteriaRepository->findAll();

    if ($request->isMethod('POST')) {
        // Récupérer les données du formulaire
        $marque = $request->request->get('marque');
        $categorie = $request->request->get('categorie');
        $prix = $request->request->get('prix');
        $critereLibelle = $request->request->get('critere'); // Récupérer le libellé du critère sélectionné
        $imageFile = $request->files->get('img')[0]; // Récupérer le fichier image

        // Débogage : Afficher le libellé du critère récupéré
        dump($critereLibelle);

        // Vérifier si une image a été téléchargée
        if ($imageFile) {
            // Récupérer le nom du fichier de l'image
            $image = $imageFile->getClientOriginalName();
            
            // Débogage : Afficher le tableau des critères numérotés
            dump($criteresNumerotes);

            // Vérifier si le libellé du critère est dans le tableau des critères numérotés
            if (isset($criteresNumerotes[$critereLibelle])) {
                // Récupérer l'ID du critère à partir du tableau des critères numérotés
                $critereId = $criteresNumerotes[$critereLibelle];

                // Récupérer l'objet Objectif correspondant à l'ID du critère
                $critere = $criteriaRepository->find($critereId);
    
                // Débogage : Afficher l'objet Objectif correspondant
                dump($critere);

                // Enregistrement des données dans la base de données
                $produit = new Produit();
                $produit->setMarque($marque);
                $produit->setCategorie($categorie);
                $produit->setPrix($prix);
                $produit->setImage($image);
                $produit->setCritere($critere); // Définir le critère
    
                // Persist et flush
                $em = $manager->getManager();
                $em->persist($produit);
                $em->flush();
    
                // Redirection vers une autre page après l'ajout
                return $this->redirectToRoute('product_all');
            }
        }
    }

    // Affichage du formulaire d'ajout avec la liste des critères
    return $this->render('product_dash/addproduit.html.twig', [
        'criteres' => $criteres,
    ]);
}

#[Route('/product/edit/{id}', name: 'edit_product')]
public function editProduct($id, Request $request): Response
{
    $entityManager = $this->getDoctrine()->getManager();
    $product = $entityManager->getRepository(Produit::class)->find($id);

    if (!$product) {
        throw $this->createNotFoundException('Le produit avec l\'identifiant '.$id.' n\'existe pas.');
    }

    $form = $this->createForm(ProduitType::class, $product);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('product_all');
    }

    return $this->render('product_dash/editproduit.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/author/delete/{id}', name: 'delete_product')]
    public function deleteAuthor($id, ManagerRegistry $manager, ProduitRepository $authorepository): Response
    {
        $em = $manager->getManager();
        $author = $authorepository->find($id);
            $em->remove($author);
            $em->flush();
            return $this->redirectToRoute('product_all');
        } 
        
    
}
