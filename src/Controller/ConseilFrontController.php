<?php

namespace App\Controller;

use App\Form\ConseilType;
use App\Form\ConseilUpdateType;
use App\Entity\Conseil;
use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ConseilRepository;

class ConseilFrontController extends AbstractController
{
    #[Route('/conseil', name: 'conseil_app')]
    public function addConseil(Request $req, ManagerRegistry $manager): Response 
    {
        $conseil = new Conseil();
        $conseil->setStatut('en attente');
        $conseil->setReponse('');
        $conseil->setNote(null);
        $utilisateur = $this->getDoctrine()->getRepository(Utilisateur::class)->find(2); //STATIQUE
        $conseil->setIdClient($utilisateur);
        $conseil->setDateConseil(new \DateTime());
        $form = $this->createForm(ConseilType::class, $conseil);
        $form->handleRequest($req);
    
        $em = $manager->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($conseil);
            $em->flush();
            
            $success = true; 
        } elseif ($form->isSubmitted()) {
            $emptySubmission = true;
        }
    
        $conseils = $this->getDoctrine()->getRepository(Conseil::class)->findBy(['id_client' => 2]);  //STATIQUE
    
        return $this->renderForm('conseil_front/add.html.twig', [
            'f' => $form,
            'conseils' => $conseils,
            'success' => $success ?? false, 
            'emptySubmission' => $emptySubmission ?? false, 
        ]);
    }
        
    #[Route('/updateNoteConseil/{id_conseil}', name: 'conseil_note_update')]
    public function update(Request $req, ManagerRegistry $manager, ConseilRepository $repo, $id_conseil): Response
    {
        $conseil = $repo->find($id_conseil);
        $form = $this->createForm(ConseilUpdateType::class, $conseil);
        $form->handleRequest($req);
    
        $em = $manager->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'La note a été modifiée avec succès.'); 
        }
    
        $conseils = $this->getDoctrine()->getRepository(Conseil::class)->findBy(['id_client' => 2]);  //STATIQUE
    
        return $this->renderForm('conseil_front/update.html.twig', [
            'conseils' => $conseils,
            'fUpdate' => $form
        ]);
    }
    
}
