<?php

namespace App\Controller;
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
    if ($request->isMethod('POST')) {
        // Récupérer les données du formulaire
        $marque = $request->request->get('marque');
        $categorie = $request->request->get('categorie');
        $prix = $request->request->get('prix');

        // Vérifier si un fichier image a été envoyé
        if ($request->files->has('img')) {
            $imageFile = $request->files->get('img')[0];
            if ($imageFile) {
                // Récupérer le nom du fichier de l'image
                $image = $imageFile->getClientOriginalName();
                
                // Enregistrement des données dans la base de données
                $prod = new Produit();
                $prod->setMarque($marque);
                $prod->setCategorie($categorie);
                $prod->setPrix($prix);
                $prod->setImage($image);

                $em = $manager->getManager();
                $em->persist($prod);
                $em->flush();

                // Redirection vers la page 'product_all'
                return $this->redirectToRoute('product_all');
            }
        }
    }

    // Affichage du formulaire d'ajout
    return $this->render('product_dash/addproduit.html.twig');

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
