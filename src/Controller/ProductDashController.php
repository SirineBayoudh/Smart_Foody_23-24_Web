<?php

namespace App\Controller;

use App\Entity\Objectif;
use App\Entity\Produit;
use App\Service\PdfGenerator;
use App\Form\ProduitType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProduitRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductDashController extends AbstractController
{
    private $pdfGenerator;

    public function __construct(PdfGenerator $pdfGenerator)
    {
        $this->pdfGenerator = $pdfGenerator;
    }

    #[Route('/product/dash', name: 'app_product_dash')]
    public function index(): Response
    {
        return $this->render('product_dash/index.html.twig', [
            'controller_name' => 'ProductDashController',
        ]);
    }

    #[Route('/product/all', name: 'product_all')]
    public function listProduit(ProduitRepository $prodrepository,PaginatorInterface $paginator,Request $request,SerializerInterface $serializer): Response
{
    // Récupérer le nombre total de produits dans la base de données
    $totalProduits = $prodrepository->countAll();

    // Calculer le nombre de pages nécessaires pour paginer les produits
    $totalPages = ceil($totalProduits / 5); // Remplacez 5 par le nombre d'éléments par page de votre pagination

    $pagination = $paginator->paginate(
        $prodrepository->createQueryBuilder('p')->orderBy('p.ref', 'DESC'),
        $request->query->getInt('page', 1),
        5 // Remplacez 5 par le nombre d'éléments par page de votre pagination
    );
    $criteres = $prodrepository->findAllCriteres();
    // Définir manuellement les catégories
    $categories = ['Fruit', 'Legume', 'Laitier', 'Grain'];

// Récupérer le nombre de produits pour chaque catégorie
$produitsParCategorie = [];
foreach ($categories as $categorie) {
    $produitsParCategorie[] = $prodrepository->countProductsByCategory($categorie);
}
    // Récupérer le nombre total de produits
    $totalProducts = $prodrepository->getTotalProducts();

    // Récupérer le total des prix des produits
    $totalPrices = $prodrepository->getTotalPrices();
    
    return $this->render('product_dash/list_produit.html.twig', [
        'totalPages' => $totalPages,
        'criteres' => $criteres,
        'pagination' => $pagination,
        'categories' => $categories,
        'productCounts' => $produitsParCategorie,
        'totalProducts' => $totalProducts,
        'totalPrices' => $totalPrices,
    ]);
}

#[Route('/product/criteres', name: 'product_criteres')]
public function getCriteres(ProduitRepository $prodrepository): JsonResponse
{
    $criteres = $prodrepository->findAllCriteres();
    return $this->json($criteres);
}

#[Route('/addproduct', name: 'add_product')]
public function addProduct(ManagerRegistry $manager, Request $request): Response
{
    $produit = new Produit();
    $form = $this->createForm(ProduitType::class, $produit);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Manipuler l'image
        $imageFile = $form->get('image')->getData();
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            // Cela sert à donner un nom unique à chaque image pour éviter les conflits de nom
            $newFilename = $originalFilename.'-'.uniqid().'.'.$imageFile->guessExtension();
            // Déplace le fichier dans le répertoire où sont stockées les images
            try {
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                // Gérer l'exception si le fichier ne peut pas être déplacé
            }
            // Met à jour le nom de l'image dans l'entité Produit
            $produit->setImage($newFilename);
        }

        // Récupérer l'EntityManager
        $entityManager = $manager->getManager();

        // Récupérer l'ID du critère sélectionné dans le formulaire
        $critereId = $form->get('critere')->getData();

        // Rechercher l'objet Objectif correspondant à l'ID
        $objectif = $entityManager->getRepository(Objectif::class)->find($critereId);

        // Affecter l'objet Objectif à la propriété critere de l'entité Produit
        $produit->setCritere($objectif);

        // Persister l'entité Produit
        $entityManager->persist($produit);
        $entityManager->flush();

        // Redirection vers une autre page après l'ajout
        return $this->redirectToRoute('product_all');
    }

    // Affichage du formulaire d'ajout
    return $this->render('product_dash/addproduit.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/product/edit/{id}', name: 'edit_product')]
public function editProduct(int $id, EntityManagerInterface $entityManager, Request $request): Response
{
    // Récupérer l'entité à modifier
    $produit = $entityManager->getRepository(Produit::class)->find($id);
    // Vérifier si l'entité existe
    if (!$produit) {
        throw $this->createNotFoundException('Le produit avec l\'ID ' . $id . ' n\'existe pas.');
    }
    

    // Créer le formulaire
    $form = $this->createForm(ProduitType::class, $produit);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Manipuler l'image
        $imageFile = $form->get('image')->getData();

        // Vérifier si un nouveau fichier a été téléchargé
        if ($imageFile instanceof UploadedFile) {
            // Générer un nom de fichier unique
            $newFilename = uniqid().'.'.$imageFile->guessExtension();

            // Déplacer le fichier vers le répertoire d'images
            try {
                $imageFile->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                // Gérer l'exception en conséquence
            }

            // Mettre à jour le nom du fichier dans l'entité
            $produit->setImage($newFilename);
        }

        // Enregistrer les modifications dans la base de données
        $entityManager->flush();

        // Rediriger vers une autre page après la modification
        return $this->redirectToRoute('product_all');
    }

    return $this->render('product_dash/editproduit.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/product/delete/{id}', name: 'delete_product')]
public function deleteProd($id, ManagerRegistry $manager, ProduitRepository $prodrepository): Response
{
    $em = $manager->getManager();
    $prod = $prodrepository->find($id);

    if ($prod !== null) {
        // Vérifier si l'objet critère est défini pour l'objectif
        $critere = $prod->getCritere();
        if ($critere !== null) {
            // Si le critère est défini, supprimer l'objectif
            $em->remove($prod);
            $em->flush();
        } else {
            // Si le critère n'est pas défini, afficher un message d'erreur ou rediriger vers une autre page
            // Exemple: return $this->redirectToRoute('page_without_criteria');
        }
    }

    return $this->redirectToRoute('product_all');
}




#[Route('/export-pdf', name: 'export_pdf')]
public function exportPdf(PdfGenerator $pdfGenerator, ProduitRepository $produitRepository)
{
    // Récupérez les données des produits depuis le repository ou un autre endroit approprié
    $products = $produitRepository->findAll();

    // Générez le PDF à partir des données des produits
    $pdfContent = $pdfGenerator->generatePdf($products);

    // Créez une réponse Symfony pour renvoyer le PDF
    $response = new Response($pdfContent);
    $response->headers->set('Content-Type', 'application/pdf');
    $response->headers->set('Content-Disposition', 'attachment; filename="liste_produits.pdf"');

    return $response;
}     
    
}
