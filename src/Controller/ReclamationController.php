<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Utilisateur;
use App\Form\ReclamationEnvoyerType;
use App\Repository\ReclamationRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request; // Utilisation de la classe correcte
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReclamationController extends AbstractController
{

       //---------------------------------------------------------------- Partie Back ---------------------------------------------------------------------------


    /**
     * @Route("/reclamations", name="reclamations")
     */
    public function index(ReclamationRepository $reclamationRepository): Response
    {
        $reclamations = $reclamationRepository->findByArchive(0);
        $archives = $reclamationRepository->findByArchive(1);
        $nombreRec = $reclamationRepository->countTotalReclamations();
        
           // Initialiser un tableau pour stocker les réclamations par mois
                $reclamationsByMonth = [];

                // Récupérer l'année actuelle
                $currentYear = date('Y');
    

                // Itérer sur les mois de l'année
                for ($month = 1; $month <= 12; $month++) {
                    // Appeler la méthode pour compter les réclamations pour le mois et l'année spécifiés
                    $reclamationsByMonth[$month] = $reclamationRepository->countReclamationsByMonthAndYear($month, $currentYear);
                }
    

        $nbTypeRec = $reclamationRepository->countReclamationsByType("Réclamation");
        $nbTypeDi = $reclamationRepository->countReclamationsByType("Demande d'information");
        $nbTypeDe = $reclamationRepository->countReclamationsByType("Demande de collaboration");
        $nbTypeRem = $reclamationRepository->countReclamationsByType("Remerciement");
        $nbTypeAut = $reclamationRepository->countReclamationsByType("Autres");


        $moyTypeRec = $reclamationRepository->averageReclamationsByType("Réclamation");
        $moyTypeDi = $reclamationRepository->averageReclamationsByType("Demande d'information");
        $moyTypeDe = $reclamationRepository->averageReclamationsByType("Demande de collaboration");
        $moyTypeRem = $reclamationRepository->averageReclamationsByType("Remerciement");
        $moyTypeAut = $reclamationRepository->averageReclamationsByType("Réclamation");


        return $this->render('reclamation/listRec.html.twig', [
            'reclamations' => $reclamations,
            'archives' => $archives,
            
            // Passage de données pour le graphe
            'reclamationsByMonth' => $reclamationsByMonth,
            

            'nombreRec' => $nombreRec,

            'nbTypeRec' => $nbTypeRec,
            'nbTypeDi' => $nbTypeDi,
            'nbTypeDe' => $nbTypeDe,
            'nbTypeRem' => $nbTypeRem,
            'nbTypeAut' => $nbTypeAut,

            'moyTypeRec' => $moyTypeRec,
            'moyTypeDi' => $moyTypeDi,
            'moyTypeDe' => $moyTypeDe,
            'moyTypeRem' => $moyTypeRem,
            'moyTypeAut' => $moyTypeAut,

        ]);
    }



   


        /**
     * @Route("/archiver-reclamation/{id}", name="archiverRec")
     */
        public function archiverRec(Request $request, int $id): Response
        {
            $entityManager = $this->getDoctrine()->getManager();
            $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);

            if (!$reclamation) {
                throw $this->createNotFoundException('La réclamation avec l\'id ' . $id . ' n\'existe pas.');
            }

            // Modifier l'état de l'archive à 1
            $reclamation->setArchive(1);

            // Enregistrer les modifications en base de données
            $entityManager->flush();

            // Redirection vers une autre page ou afficher un message de succès
            return $this->redirectToRoute('reclamations');
        }


          /**
         * @Route("/desarchiver-reclamation/{id}", name="desarchiverRec")
         */
        public function desarchiverRec(Request $request, int $id): Response
        {
            $entityManager = $this->getDoctrine()->getManager();
            $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);

            if (!$reclamation) {
                throw $this->createNotFoundException('La réclamation avec l\'id ' . $id . ' n\'existe pas.');
            }

            // Modifier l'état de l'archive à 1
            $reclamation->setArchive(0);

            // Enregistrer les modifications en base de données
            $entityManager->flush();

            // Redirection vers une autre page ou afficher un message de succès
            return $this->redirectToRoute('reclamations');
        }


       /**
     * @Route("/repondre-reclamation/{id}", name="repondreRec")
     */
    public function repondreRec(Request $request, int $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);

        // Création du formulaire
        $form = $this->createFormBuilder()
            ->add('reclamation_id', HiddenType::class, [
                'data' => $id, // On passe l'ID de la réclamation au formulaire
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['readonly' => true, 'class' => 'form-control'], // Le champ sera en lecture seule
                'data' => $reclamation ? $reclamation->getNom() : null,
                'disabled' => true,
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['readonly' => true, 'class' => 'form-control'], // Le champ sera en lecture seule
                'data' => $reclamation ? $reclamation->getPrenom() : null,
                'disabled' => true,
            ])
            ->add('type', TextType::class, [
                'label' => 'Type',
                'attr' => ['readonly' => true, 'class' => 'form-control'], // Le champ sera en lecture seule
                'data' => $reclamation ? $reclamation->getType() : null,
                'disabled' => true,
            ])
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => ['readonly' => true, 'class' => 'form-control'], // Le champ sera en lecture seule
                'data' => $reclamation ? $reclamation->getTitre() : null,
                'disabled' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['readonly' => true, 'class' => 'form-control'], // Le champ sera en lecture seule
                'data' => $reclamation ? $reclamation->getDescription() : null,
                'disabled' => true,
            ])
            // Ajoutez les autres champs nécessaires pour la réponse
            ->add('reponse', TextareaType::class, [
                'label' => 'Réponse',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer',
            ])
            ->getForm();

        // Gérer la soumission du formulaire
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $reponse = new Reponse();
            // Assigner les valeurs du formulaire à votre entité Reponse et les sauvegarder

            // Redirection ou autre traitement
        }

        // Afficher le formulaire dans votre vue
        return $this->render('listRec.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    //---------------------------------------------------------------- Partie FRONT ---------------------------------------------------------------------------

    /**
     * @Route("/addRec", name="addRec")
     */
    public function ajoutRec(Request $request, ReclamationRepository $reclamationRepository, UtilisateurRepository $utilisateurRepository): Response
    {
        // Utiliser la méthode prepareReclamationFormForUser7() du repository ReclamationRepository pour obtenir l'utilisateur avec l'ID 7
        $userId = 15; // ID de l'utilisateur souhaité
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


        $emptySubmission = false;
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