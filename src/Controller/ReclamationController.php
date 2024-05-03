<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Utilisateur;
use App\Form\ReclamationEnvoyerType;
use App\Repository\ReclamationRepository;
use App\Repository\UtilisateurRepository;
use App\Service\BadWordsChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request; // Utilisation de la classe correcte
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;


use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email; 

use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use App\Service\PhpBadWords; // Importer la classe PhpBadWords

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
 * @Route("/repondre_reclamation/{id}", name="repondre_reclamation", methods={"GET", "POST"})
 */
public function repondreReclamation(EntityManagerInterface $entityManager, Request $request, MailerInterface $mailer, $id): Response
{
    $reclamation = $entityManager->getRepository(Reclamation::class)->find($id);

    if (!$reclamation) {
        throw $this->createNotFoundException('La réclamation n\'existe pas.');
    }
    
    // Récupérer la réponse depuis la requête
    $reponse = $request->get('reponse');

    //récupérer la description du message 
   $desc = $reclamation->getDescription();

   //récupérer la date du message 
   $dateR = $reclamation->getDateReclamation();

   // Formater la date au format souhaité (par exemple, jour/mois/année)
    $dateReclamationFormattee = date("d/m/Y", strtotime($dateR));

   //récupérer le nom du client
   $name = $reclamation->getIdClient()->getNom();


// Construction du contenu de l'email
$emailContent = "
<html>
    <body>
        <div style='width: 500px; background-color: #aad597; padding: 20px;'>
            <p style='color: darkgreen; font-size: 14px;'>Bonjour M./Mme $name,</p>
            <p style='color: darkgreen; font-size: 18px;'>Voici la Réponse : " .$reponse. " </p>
            <p style='color: black; font-weight: bold; font-size: 16px;'>Suite à votre message : $desc</p>
            <p style='color: darkgreen; font-weight: bold; font-size: 16px;'>Date de réception : " .$dateReclamationFormattee. "</p>
            <p style='font-weight: bold;'> <em> Merci pour l'intérêt que vous portez à Smart foody! </em>  </p>
            
            <td align='left' class='esd-block-image es-p5t es-p5b es-m-txt-c' style='font-size: 0px;'>
            <a target='_blank' href='https://viewstripo.email'>
                <img src='https://eetnmyy.stripocdn.email/content/guids/CABINET_02d1bc47a643a3e7bfe02b0f41d6cb58a6c2703f13c0ecd11cddd42b47af504e/images/image.png' alt='Logo' style='display:block' height='45' title='Logo' class='adapt-img'>
            </a>
            </td>
            <p style='color: gray;'> <br> <em>   Bonne compréhension ! </em> </p>
        </div>
    </body>
</html>
";

    // Envoyer un e-mail de réponse
    $email = (new Email())
        ->from('smartfoody.2024@gmail.com')
        ->to("divinpoadzola@gmail.com") // Supposons que la méthode getClient() retourne l'entité Client associée à la réclamation
        ->subject('Réponse à votre réclamation')
        ->html($emailContent);
        $mailer->send($email);
    try {
        
        // Mettre à jour l'état de la réclamation seulement si l'e-mail est envoyé avec succès
        $reclamation->setStatut('Répondu');
        
        // Enregistrer les changements dans la base de données
        $entityManager->persist($reclamation);
        $entityManager->flush();
        // Ajouter un message flash pour confirmer l'envoi de la réclamation
        $this->addFlash('success', 'Reponse envoyé avec succes!');
        // Rediriger ou renvoyer une réponse JSON
        // ...
        
        return $this->redirectToRoute('reclamations');
    } catch (TransportExceptionInterface $e) {
        // Gérer l'erreur, par exemple en journalisant ou en affichant un message à l'utilisateur
        // Vous pouvez également renvoyer une réponse d'erreur appropriée
        return new Response('Erreur lors de l\'envoi de l\'e-mail de réponse: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}


  /**
 * @Route("/export", name="export_to_excel")
 */
public function exportToExcel(ReclamationRepository $reclamationRepository)
{   
    // Récupérer les réclamations
    $reclamations = $reclamationRepository->findByArchive(0);
    
    // Générer le contenu CSV avec une ligne de titre explicite
    $csvContent = "Id Reclamation,Description,Titre,Statut,Type,Date Reclamation\n";
    foreach ($reclamations as $reclamation) {
        $dateReclamation = $reclamation->getDateReclamation();
        $dateFormatted = $dateReclamation ? $dateReclamation->format('Y-m-d') : '';
        $csvContent .= $reclamation->getId() . ',' . 
                       $reclamation->getDescription() . ',' . 
                       $reclamation->getTitre() . ',' . 
                       $reclamation->getStatut() . ',' . 
                       $reclamation->getType() . ',' . 
                       $dateFormatted . "\n";
    }

    // Créer une réponse avec le contenu CSV
    $response = new Response($csvContent);
    $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
    $response->headers->set('Content-Disposition', 'attachment; filename="reclamations.csv"');

    return $response;
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
            $reponse = new Response();
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

          // Vérifier la présence de gros mots dans la description de la réclamation
          $description = $reclamation->getDescription();
          $containsBadWord = 0;
  
          // Créer une instance de PhpBadWords
          $badWordsChecker = new PhpBadWords();
          $badWordsChecker->setDictionaryFromFile(__DIR__ . "/../Service/listeMots.php");
          $badWordsChecker->setText($description);
  
          if ($badWordsChecker->check()) {
              // Si un mot interdit est trouvé, mettre la variable à 1
              $containsBadWord = 1;
              // Afficher un message d'erreur
              $this->addFlash('error', 'Le texte contient des gros mots. Veuillez réformuler votre message.');
              // Rediriger vers la page du formulaire sans enregistrer la réclamation
              return $this->redirectToRoute('addRec');
          }


        elseif($form->isSubmitted() && $form->isValid()) {
            
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
// Récupérer les réclamations en attente de l'utilisateur
$reclamations = $reclamationRepository->findBy(['id_client' => $user, 'statut' => 'Attente']);


        // Afficher le formulaire dans le template
        return $this->render('reclamation/ajout_rec.html.twig', [
            'form' => $form->createView(),
            'reclamations' =>$reclamations,
            'containsBadWord' => $containsBadWord, // Passer la variable au twig pour le badWords
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

    // Ajouter un message flash pour confirmer la supression de la réclamation
    $this->addFlash('success', 'Votre réclamation a été supprimée');

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