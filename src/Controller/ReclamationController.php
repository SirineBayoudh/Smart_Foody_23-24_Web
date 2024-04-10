<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Utilisateur;
use App\Form\ReclamationEnvoyerType;
use App\Repository\ReclamationRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request; // Utilisation de la classe correcte
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReclamationController extends AbstractController
{
    /**
     * @Route("/reclamations", name="reclamations")
     */
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        $reclamations = $reclamationRepository->findByArchive(0);

        return $this->render('reclamation/listRec.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    /**
     * @Route("/addRec", name="addRec")
     */
    public function ajoutRec(Request $request, ReclamationRepository $reclamationRepository, UtilisateurRepository $utilisateurRepository): Response
    {
        // Utiliser la méthode prepareReclamationFormForUser7() du repository ReclamationRepository pour obtenir l'utilisateur avec l'ID 7
        $userId = 7; // ID de l'utilisateur souhaité
        $user = $reclamationRepository->prepareReclamationFormForUser7($userId, $utilisateurRepository);

        // Créer une instance de Reclamation
        $reclamation = new Reclamation();

        // Créer le formulaire en passant l'utilisateur
        $form = $this->createForm(ReclamationEnvoyerType::class, $reclamation, [
            'user' => $user, // Passer l'utilisateur au formulaire
        ]);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        // Récupérer les réclamations en attente de l'utilisateur
        $reclamations = $reclamationRepository->findBy(['id_client' => $user, 'statut' => 'Attente']);


        if ($form->isSubmitted() && $form->isValid()) {
            // Créer une nouvelle réclamation avec les données du formulaire
            $nouvelleReclamation = new Reclamation();
            $nouvelleReclamation->setDescription($reclamation->getDescription());
            $nouvelleReclamation->setTitre($reclamation->getTitre());
            $nouvelleReclamation->setType($reclamation->getType());
            $nouvelleReclamation->setIdClient($user); // Assigner l'utilisateur à la réclamation

            // Enregistrer la réclamation dans la base de données
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($nouvelleReclamation);
            $entityManager->flush();

             // Ajouter un message flash pour confirmer l'envoi de la réclamation
                $this->addFlash('success', 'Votre réclamation a bien été envoyée!');

            // Rediriger vers une autre page après la création de la réclamation
            return $this->redirectToRoute('addRec');
        }

        // Afficher le formulaire dans le template
        return $this->render('reclamation/ajout_rec.html.twig', [
            'form' => $form->createView(),
            'reclamations' =>$reclamations,
        ]);
    }


    /**
 * @Route("/supprimer-reclamation/{id}", name="suppRec")
 */
public function supprimerRec(Request $request, int $id): Response
{
    $entityManager = $this->getDoctrine()->getManager();
    $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);

    if (!$reclamation) {
        throw $this->createNotFoundException('Aucunne reclamation trouvé pour l\'identifiant '.$id);
    }

    $entityManager->remove($reclamation);
    $entityManager->flush();

    // Redirection vers une autre page ou afficher un message de succès
    return $this->redirectToRoute('addRec');
}

  /**
 * @Route("/modifier-reclamation/{id}", name="modifierRec")
 */
public function modifierRec(Request $request, int $id, ReclamationRepository $reclamationRepository, UtilisateurRepository $utilisateurRepository): Response
{
    // Récupérer la réclamation à modifier
    $reclamation = $reclamationRepository->find($id);

    // Vérifier si la réclamation existe
    if (!$reclamation) {
        throw $this->createNotFoundException('Aucune réclamation trouvée pour l\'identifiant '.$id);
    }

    // Récupérer l'utilisateur associé à la réclamation
    $user = $reclamation->getIdClient();

    // Récupérer les réclamations en attente de l'utilisateur
    $reclamations = $reclamationRepository->findBy(['id_client' => $user, 'statut' => 'Attente']);

    // Créer le formulaire en passant la réclamation et l'utilisateur
    $form = $this->createFormBuilder($reclamation)
        ->add('nom', TextType::class, [
            'data' => $user->getNom(),
            'attr' => ['readonly' => true, 'class' => 'form-control'],
            'label' => 'Nom : ',
        ])
        ->add('prenom', TextType::class, [
            'data' => $user->getPrenom(),
            'attr' => ['readonly' => true],
            'label' => 'Prénom : ',
        ])
        ->add('email', TextType::class, [
            'data' => $user->getEmail(),
            'attr' => ['readonly' => true],
            'label' => 'Email :',
        ])
        ->add('type', ChoiceType::class, [
            'choices' => [
                'Réclamation' => 'Réclamation',
                "Demande d'information" => "Demande d'information",
                "Remerciement" => "Remerciement",
                "Demande de Collaboration" => "Demande de Collaboration",
                "Autres" => "Autres",
            ],
            'label' => 'Type :',
            'data' => $reclamation->getType(),
        ])
        ->add('titre', TextType::class, [
            'data' => $reclamation->getTitre(),
            'label' => 'Titre :',
        ])
        ->add('description', TextareaType::class, [
            'data' => $reclamation->getDescription(),
            'label' => 'Votre message :',
        ])
        ->getForm();

    // Gérer la soumission du formulaire
    $form->handleRequest($request);

    // Vérifier si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
        // Enregistrer les modifications dans la base de données
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        // Ajouter un message flash pour confirmer la modification de la réclamation
        $this->addFlash('success', 'Votre réclamation a bien été modifiée!');

        // Rediriger vers une autre page après la modification de la réclamation
        return $this->redirectToRoute('addRec');
    }

    // Afficher le formulaire dans le template
    return $this->render('reclamation/modifier_rec.html.twig', [
        'form' => $form->createView(),
        'reclamations' => $reclamations, // Passer les réclamations au template
        
    ]);
}

}