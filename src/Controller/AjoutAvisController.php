<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Entity\Produit;
use App\Form\AvisNoteTypType;
use App\Repository\AvisRepository;
use App\Repository\ProduitRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class AjoutAvisController extends AbstractController
{


  
        //---------------------------------------------------------------- Partie Back ---------------------------------------------------------------------------
     
        /**
         * @Route("/avisBack", name="avisBack")
         */
        public function index(Request $request, AvisRepository $avisRepository): Response
        {
            // Récupérer la variable yValues si elle est passée
            $yValues = $request->query->get('yValues');

            // Si yValues n'est pas défini, le mettre à zéro
            if ($yValues === null) {
                $yValues = '0,0,0,0,0'; // Ou toute autre valeur par défaut que vous souhaitez
            }

            //stat par notes
            $counts = [];
            for ($i = 1; $i <= 5; $i++) {
                $counts[$i] = $avisRepository->countAvisByRating($i);
            }

             // Appel de la fonction countTotalAvis du repository pour obtenir le nombre total d'avis
                 $totalAvis = $avisRepository->countTotalAvis();

            // Récupérer tous les avis
            $avis = $avisRepository->findAll();

            // Récupérer la liste de tous les produits
            $produits = $avisRepository->findAllProducts();

            return $this->render('avis/listAvis.html.twig', [
                'Avis' => $avis,
                'yValues' => $yValues, // Passer yValues au template
                'produits' => $produits, // Passer la liste des produits au template
                'counts' => $counts,
                'totalAvis' => $totalAvis,
            ]);
        }


    /**
     * @Route("/calculPersonne/{ref_produit}", name="calculPersonne")
     */
    public function calculPersonne(Request $request, string $ref_produit, AvisRepository $avisRepository): Response
    {
        // Utilisez la référence du produit passée en tant que paramètre dans la route
        $refProduit = $ref_produit;
        $data = [];
        
        
        // Boucle pour chaque note de 1 à 5
        for ($note = 1; $note <= 5; $note++) {
            // Utilisez la méthode countAvisByProductAndRating pour obtenir le nombre de personnes ayant donné la même note pour un produit donné
            $nombrePersonnes = $avisRepository->countAvisByProductAndRating($refProduit, $note);
            $data[] = $nombrePersonnes;
        }

        $yValues = implode(',', $data); // Les nombres de personnes correspondants

        // Rediriger vers la route "avisBack" tout en passant les valeurs nécessaires
        return $this->redirectToRoute('avisBack', [
            'yValues' => $yValues,
        ]);
    }

      /**
     * @Route("/deleteAvis/{id_avis}", name="deleteAvis")
     */
    public function deleteAvis(int $id_avis, EntityManagerInterface $entityManager): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $avis = $entityManager->getRepository(Avis::class)->find($id_avis);

        if (!$avis) {
            throw $this->createNotFoundException('Aucun avis trouvé pour l\'identifiant '.$id_avis);
        }

        $entityManager->remove($avis);
        $entityManager->flush();

        // Redirection vers une autre page ou afficher un message de succès
        return $this->redirectToRoute('avisBack');
    }

    /**
     * @Route("/refreshSignal/{id_avis}", name="refreshSignal")
     */
    public function refreshSignal(int $id_avis, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'avis à partir de son ID
        $avis = $entityManager->getRepository(Avis::class)->find($id_avis);

        // Vérifier si l'avis existe
        if (!$avis) {
            // Gérer le cas où l'avis n'est pas trouvé, par exemple, rediriger vers une page d'erreur
            // ou afficher un message d'erreur
            return $this->redirectToRoute('avisBack');
        }

        // Mettre à zéro la valeur de "signaler" pour cet avis
        $avis->setSignaler(0);

        // Enregistrer les modifications dans la base de données
        $entityManager->flush();

        // Rediriger vers une autre page ou afficher un message de succès
        return $this->redirectToRoute('avisBack');
    }
    
//-------------------------------------------------Produit--------------------------------------------------------------------------------------
        /**
     * @Route("/afficheP", name="affiche_P")
     */
    /*public function afficherp(ProduitRepository $produitRepository, AvisRepository $avisRepository): Response
        {
             $produit = new Produit();

        
            // Récupérer tous les produits en utilisant la méthode findAll
                    $produits = $produitRepository->findAll();

            return $this->render('avis/produitaffiche.html.twig', [
                'produit' => $produit,
            ]);
        }*/
                /**
                 * @Route("/produitaff/{ref}", name="produit_aff")
                 */
                public function nouveauProduit(string $ref, Request $request, UtilisateurRepository $utilisateurRepository, ProduitRepository $produitRepository, AvisRepository $avisRepository): Response
                {
                    // Récupérer l'utilisateur
                    $user = $this->prepareReclamationFormForUser7(7, $utilisateurRepository);
                    
                    // Récupérer le produit par sa référence
                    $produit = $produitRepository->findOneBy(['ref' => $ref]);

                    // Vérifier si le produit existe
                    if (!$produit) {
                        throw $this->createNotFoundException('Le produit n\'existe pas.');
                    }

                    // Créer une nouvelle instance d'Avis
                    $avis = new Avis();

                    // Associer l'utilisateur et le produit à l'avis
                    $avis->setIdClient($user);
                    $avis->setRefProduit($produit);

                    // Créer le formulaire
                    $form = $this->createForm(AvisNoteTypType::class, $avis);
                    
                    $form->handleRequest($request);

                    if ($form->isSubmitted() && $form->isValid()) {
                        $entityManager = $this->getDoctrine()->getManager();
                        $entityManager->persist($avis);
                        $entityManager->flush();

                        // Rediriger ou afficher un message de succès
                        return $this->redirectToRoute('avis_nouveau');
                    }

                    // Récupérer les quatre derniers avis pour le produit
                    $lastFourAvis = $avisRepository->findByproduit($produit->getRef());

                    // Appeler la fonction pour calculer le nombre d'avis et la moyenne des étoiles
                    $calculAvis = $this->calculerAvis($produit->getRef(), $avisRepository);

                    // Récupérer les quatre produits similaires
                    $similarProducts = $produitRepository->findFourSimilarProducts($produit);

                    return $this->render('avis/produitSingle.html.twig', [
                        'form' => $form->createView(),
                        'user' => $user,
                        'produit' => $produit,
                        'avis' => $avis, // Passer les avis au modèle Twig
                        'nombre_avis' => $calculAvis['nombre_avis'],
                        'moyenne_etoiles' => $calculAvis['moyenne_etoiles'],
                        'last_four_avis' => $lastFourAvis, // Passer les quatre derniers avis au modèle Twig
                        'similar_products' => $similarProducts, // Passer les produits similaires au modèle Twig
                    ]);
                }








        //---------------------------------------------------------------- Partie FRONT ---------------------------------------------------------------------------


    /**
     * @Route("/NouvelAvis", name="avis_nouveau")
     */
    public function nouveau(Request $request, UtilisateurRepository $utilisateurRepository, ProduitRepository $produitRepository, AvisRepository $avisRepository): Response
            {
            // Récupérer l'utilisateur et le produit
            $user = $this->prepareReclamationFormForUser7(7, $utilisateurRepository);
            $produit = $this->prepareReclamationFormProduit(102, $produitRepository);

            // Créer une nouvelle instance d'Avis
            $avis = new Avis();

            // Associer l'utilisateur et le produit à l'avis
            $avis->setIdClient($user);
            $avis->setRefProduit($produit);

            // Créer le formulaire
            $form = $this->createForm(AvisNoteTypType::class, $avis);
            
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($avis);
                $entityManager->flush();

                // Rediriger ou afficher un message de succès
                return $this->redirectToRoute('avis_nouveau');
            }

            // Récupérer les quatre derniers avis pour le produit
            $lastFourAvis = $avisRepository->findByproduit($produit->getRef());

            // Appeler la fonction pour calculer le nombre d'avis et la moyenne des étoiles
            $calculAvis = $this->calculerAvis($produit->getRef(), $avisRepository);

            // Récupérer les quatre produits similaires
            $similarProducts = $produitRepository->findFourSimilarProducts($produit);

            return $this->render('avis/produitSingle.html.twig', [
                'form' => $form->createView(),
                'user' => $user,
                'produit' => $produit,
                'avis' => $avis, // Passer les avis au modèle Twig
                'nombre_avis' => $calculAvis['nombre_avis'],
                'moyenne_etoiles' => $calculAvis['moyenne_etoiles'],
                'last_four_avis' => $lastFourAvis, // Passer les quatre derniers avis au modèle Twig
                'similar_products' => $similarProducts, // Passer les produits similaires au modèle Twig
            ]);
        }



/**
 * @Route("/modifier-avis/{id}", name="modifier_avis")
 */
public function modifierAvis(Request $request, int $id): Response
{
    $entityManager = $this->getDoctrine()->getManager();
    $avis = $entityManager->getRepository(Avis::class)->find($id);

    if (!$avis) {
        throw $this->createNotFoundException('Aucun avis trouvé pour l\'identifiant '.$id);
    }

    // Créer le formulaire de modification d'avis
    $form = $this->createFormBuilder($avis)
    ->add('nb_etoiles', ChoiceType::class, [
        'label' => 'Note : ',
        'choices' => [
            '1' => 1,
            '2' => 2,
            '3' => 3,
            '4' => 4,
            '5' => 5,
        ],
    ])
    ->add('commentaire', TextareaType::class, [
        'label' => 'Commentaire : '
    ])
    ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('avis_nouveau');
    }

    return $this->render('avis/modifier.html.twig', [
        'form' => $form->createView(),
    ]);
}

 /**
 * @Route("/supprimer-avis/{id}", name="supprimer_avis")
 */
public function supprimerAvis(Request $request, int $id): Response
{
    $entityManager = $this->getDoctrine()->getManager();
    $avis = $entityManager->getRepository(Avis::class)->find($id);

    if (!$avis) {
        throw $this->createNotFoundException('Aucun avis trouvé pour l\'identifiant '.$id);
    }

    $entityManager->remove($avis);
    $entityManager->flush();

    // Redirection vers une autre page ou afficher un message de succès
    return $this->redirectToRoute('avis_nouveau');
}

/**
 * @Route("/signaler-avis/{id}", name="signaler_avis")
 */
public function signalerAvis(Request $request, int $id): Response
{
    $entityManager = $this->getDoctrine()->getManager();
    $avis = $entityManager->getRepository(Avis::class)->find($id);

    if (!$avis) {
        throw $this->createNotFoundException('Aucun avis trouvé pour l\'identifiant '.$id);
    }

    // Incrémenter la valeur de la propriété "signaler" de l'avis
    $avis->setSignaler($avis->getSignaler() + 1);

    // Persistez les changements
    $entityManager->persist($avis);
    $entityManager->flush();

    // Redirection vers une autre page ou afficher un message de succès
    return $this->redirectToRoute('avis_nouveau');
}



  /**
 * @Route("/calculerAvis/{idProduit}", name="calculer_avis")
 */
public function calculerAvis($idProduit, AvisRepository $avisRepository): array
{
    // Récupérer tous les avis pour le produit spécifié
    $avis = $avisRepository->findBy(['ref_produit' => $idProduit]);
    
    // Initialiser le nombre total d'avis et la somme des étoiles
    $totalAvis = count($avis);
    $sumEtoiles = 0;

    // Calculer la somme des étoiles
    foreach ($avis as $avisItem) {
        $sumEtoiles += $avisItem->getNbEtoiles();
    }

    // Calculer la moyenne des étoiles
    if ($totalAvis > 0) {
        $moyenneEtoiles = round($sumEtoiles / $totalAvis, 1);
    } else {
        $moyenneEtoiles = 0; // éviter la division par zéro
    }

    // Retourner un tableau contenant le nombre total d'avis et la moyenne des étoiles
    return [
        'nombre_avis' => $totalAvis,
        'moyenne_etoiles' => $moyenneEtoiles
    ];
}



    //---------------------------------------------------------------- Partie Repository ---------------------------------------------------------------------------



//recuperer les informations de l'utilisateur
public function prepareReclamationFormForUser7(int $userId, UtilisateurRepository $utilisateurRepository)
{
    // Fetch the user with the provided ID
    $user = $utilisateurRepository->find($userId);

    return $user;
}


//Recupérer les informations du produit
public function prepareReclamationFormProduit(int $refProduit, ProduitRepository $produitRepository)
{
    // Fetch the user with the provided ID
    $ref = $produitRepository->find($refProduit);

    return $ref;
}

}
