<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Form\AvisNoteTypType;
use App\Repository\AvisRepository;
use App\Repository\ProduitRepository;
use App\Repository\UtilisateurRepository;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class AjoutAvisController extends AbstractController
{

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
        ->add('nb_etoiles', IntegerType::class, [
            'label' => 'Note : '
        ])
        ->add('commentaire', TextareaType::class, [
            'label' => 'Commentaire : '
        ])
        //->add('save', SubmitType::class, ['label' => 'Modifier'])
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
     * @Route("/listAvis", name="list_avis")
     */
    public function afficheList(AvisRepository $avisRepository, UtilisateurRepository $utilisateurRepository): Response
    {
        // Appel à la fonction pour récupérer l'utilisateur connecté
        $user = $this->prepareReclamationFormForUser7(7,$utilisateurRepository);

        $avis = $avisRepository->findByproduit(102);

        return $this->render('avis/list.html.twig', [
            'avis' => $avis,
            'user' => $user,
        ]);
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
