<?php

namespace App\Controller;

use App\Entity\Objectif;
use App\Form\ObjectifType;
use App\Repository\ObjectifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ObjectifDashController extends AbstractController
{
    #[Route('/objectif/dash', name: 'app_objectif_dash')]
    public function index(): Response
    {
        return $this->render('objectif_dash/index.html.twig', [
            'controller_name' => 'ObjectifDashController',
        ]);
    }

    #[Route('/objectif/all', name: 'objectif_all')]
    public function listProduit(ObjectifRepository $prodrepository): Response
{
    return $this->render('objectif_dash/list_objectif.html.twig', [
        'obj' => $prodrepository->findAll(),
    ]);
}

#[Route('/addobject', name: 'add_object')]
public function addProduct(ManagerRegistry $manager, Request $request): Response
{
    if ($request->isMethod('POST')) {
        // Récupérer les données du formulaire
        $libelle = $request->request->get('objectif');
        $selectedCriteres = $request->request->get('listCritereConcatenated');
        
        // Concaténer les valeurs sélectionnées avec une virgule
        $listCritereConcatenated = implode(",", $selectedCriteres);
                
        // Enregistrement des données dans la base de données
        $obj = new Objectif();
        $obj->setLibelle($libelle);
        $obj->setListCritere($listCritereConcatenated);

        $em = $manager->getManager();
        $em->persist($obj);
        $em->flush();

        // Redirection vers la page 'objectif_all'
        return $this->redirectToRoute('objectif_all');
    }

    // Affichage du formulaire d'ajout
    return $this->render('objectif_dash/addobjectif.html.twig');
}

#[Route('/editobjectif/{id}', name: 'edit_objectif')]
public function editObjectif(int $id, Request $request): Response
{
    $entityManager = $this->getDoctrine()->getManager();
    $objectif = $entityManager->getRepository(Objectif::class)->find($id);

    if (!$objectif) {
        throw $this->createNotFoundException('Objectif non trouvé pour l\'id '.$id);
    }

    // Définir les libellés prédéfinis
    $libelles = ['Bien-être', 'Perte de poids', 'Prise de poids', 'Prise de masse musculaire'];

    $criteres = explode(',', $objectif->getListCritere());

    // Vérifier si le formulaire a été soumis
    if ($request->isMethod('POST')) {
        // Récupérer les données du formulaire
        $libelle = $request->request->get('libelle');
        $criteresSelectionnes = $request->request->get('criteres');

        // Mettre à jour les propriétés de l'objectif
        $objectif->setLibelle($libelle);
        $objectif->setListCritere(implode(',', $criteresSelectionnes));

        // Enregistrer les modifications dans la base de données
        $entityManager->flush();

        // Rediriger vers une autre page après la mise à jour
        return $this->redirectToRoute('objectif_all');
    }

    return $this->render('objectif_dash/editobjectif.html.twig', [
        'objectif' => $objectif,
        'libelles' => $libelles,
        'criteres' => $criteres,
    ]);
}


#[Route('/objectif/delete/{id}', name: 'delete_objectif')]
    public function deleteAuthor($id, ManagerRegistry $manager, ObjectifRepository $objrepository): Response
    {
        $em = $manager->getManager();
        $obj = $objrepository->find($id);
            $em->remove($obj);
            $em->flush();
            return $this->redirectToRoute('objectif_all');
        } 
        
    
}
