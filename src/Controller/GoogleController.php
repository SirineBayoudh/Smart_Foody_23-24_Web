<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoogleController extends AbstractController
{
    /**
     * @Route("/connect/google", name="connect_google")
     */
    public function connectToGoogle(): Response
    {
        // Rediriger vers le service d'authentification Google
        return $this->get('oauth2.registry')
            ->getClient('google')
            ->redirect([], []);
    }

    /**
     * @Route("/connect/google/check", name="connect_google_check")
     */
    public function connectToGoogleCheck(): Response
    {
        // Cette méthode sera appelée après que l'utilisateur a autorisé l'accès à Google

        // Récupérer les informations de l'utilisateur depuis Google
        $googleUser = $this->get('oauth2.registry')
            ->getClient('google')
            ->fetchUser();

        // Traiter les informations de l'utilisateur, par exemple, vous pouvez les enregistrer dans la base de données

        // Rediriger l'utilisateur vers une autre page
        return $this->redirectToRoute('home');
    }
}
