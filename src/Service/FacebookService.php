<?php

namespace App\Service;

use Facebook\Facebook;

class FacebookService
{
    private $facebook;

    public function __construct(Facebook $facebook)
    {
        $this->facebook = $facebook;
    }
    public function postToFacebook($message)
    {
        try {
            $response = $this->facebook->post('/me/feed', ['message' => $message]);
            return true; // Succès
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // Gérer les erreurs de réponse
            return false; // Échec
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // Gérer les erreurs du SDK Facebook
            return false; // Échec
        }
    }
    public function getPosts($userId, $accessToken)
    {
        try {
            // Utilisez l'access token fourni pour accéder aux données de l'utilisateur
            $response = $this->facebook->get('/' . $userId . '/posts', $accessToken);
            // Récupérez le corps de la réponse décodée
            $posts = $response->getDecodedBody();
            return $posts;
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            // Gérer les erreurs de réponse (par exemple, accès non autorisé, utilisateur non trouvé, etc.)
            // Vous pouvez journaliser l'erreur ou renvoyer une réponse d'erreur appropriée
            return ['error' => 'Erreur de réponse Facebook: ' . $e->getMessage()];
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            // Gérer les erreurs du SDK Facebook (par exemple, erreur de configuration, erreur de connexion, etc.)
            // Vous pouvez journaliser l'erreur ou renvoyer une réponse d'erreur appropriée
            return ['error' => 'Erreur SDK Facebook: ' . $e->getMessage()];
        }
    }
}
