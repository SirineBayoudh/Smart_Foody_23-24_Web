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
        $objectifs = $this->getDoctrine()->getRepository(Objectif::class)->findBy([], ['id_obj' => 'DESC']);
        
        // Récupérer la liste des critères
        $criteres = $prodrepository->findAllCriteres(); // Remplacez cela par votre propre méthode pour récupérer les critères
        
        return $this->render('objectif_dash/list_objectif.html.twig', [
            'obj' => $objectifs,
            'criteres' => $criteres, // Passer les critères à votre modèle Twig
        ]);
    }
    

#[Route('/addobject', name: 'add_object')]
public function addProduct(Request $request): Response
{
    $objectif = new Objectif();
    $form = $this->createForm(ObjectifType::class, $objectif);
    $form->handleRequest($request);

    $emptySubmission = false;

    if ($form->isSubmitted()) {
        // Récupérer les données du formulaire
        $selectedCriteres = $form->get('listCritere')->getData();
        
        // Vérifier si au moins un critère est sélectionné
        if (empty($selectedCriteres)) {
            $emptySubmission = true;
        } elseif ($form->isValid()) {
            // Filtrer les valeurs pour ne conserver que celles qui sont cochées
            $checkedCriteres = array_filter($selectedCriteres);

            // Concaténer les valeurs sélectionnées dans une chaîne séparée par des virgules
            $listCritereConcatenated = implode(",", $checkedCriteres);
            
            // Assigner la chaîne de caractères à la propriété listCritere
            $objectif->setListCritere($listCritereConcatenated);
            
            // Enregistrement des données dans la base de données
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($objectif);
            $entityManager->flush();

            // Redirection vers la page 'objectif_all'
            return $this->redirectToRoute('objectif_all');
        }
    }

    // Affichage du formulaire d'ajout
    return $this->render('objectif_dash/addobjectif.html.twig', [
        'form' => $form->createView(),
        'emptySubmission' => $emptySubmission,
    ]);
}





#[Route('/editobjectif/{id}', name: 'edit_objectif')]
public function editObjectif(int $id, Request $request): Response
{
    $entityManager = $this->getDoctrine()->getManager();
    $objectif = $entityManager->getRepository(Objectif::class)->find($id);

    if (!$objectif) {
        throw $this->createNotFoundException('Objectif non trouvé pour l\'id '.$id);
    }

    // Récupérer la chaîne de critères de l'objet Objectif
    $listeCritereString = $objectif->getListCritere();

    // Transformer la chaîne en tableau de critères
    $selectedCriteres = explode(',', $listeCritereString);

    $form = $this->createForm(ObjectifType::class, $objectif);

    // Pré-cocher les cases à cocher dans le formulaire avec les valeurs sélectionnées
    $form->get('listCritere')->setData($selectedCriteres);

    $form->handleRequest($request);

    $emptySubmission = false;

    if ($form->isSubmitted()) {
        // Vérifier si au moins un critère est sélectionné
        if (empty($form->get('listCritere')->getData())) {
            $emptySubmission = true;
        } elseif ($form->isValid()) {
            // Mettre à jour la chaîne de critères avec les nouvelles valeurs sélectionnées
            $listeCritereString = implode(',', $form->get('listCritere')->getData());
            $objectif->setListCritere($listeCritereString);

            $entityManager->flush();

            // Redirection après la mise à jour
            return $this->redirectToRoute('objectif_all');
        }
    }

    // Affichage du formulaire de modification
    return $this->render('objectif_dash/editobjectif.html.twig', [
        'form' => $form->createView(),
        'emptySubmission' => $emptySubmission,
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
