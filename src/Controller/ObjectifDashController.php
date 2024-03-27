<?php

namespace App\Controller;

use App\Entity\Objectif;
use App\Repository\ObjectifRepository;
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
}
