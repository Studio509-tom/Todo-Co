<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
// Contrôleur de sécurité : gère la connexion et la déconnexion des utilisateurs
class SecurityController extends AbstractController
{
    // Affiche le formulaire de connexion et gère l'authentification
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Récupère la dernière erreur de connexion (si existante)
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupère le dernier nom d'utilisateur saisi
        $lastUsername = $authenticationUtils->getLastUsername();

        // Affiche le template de connexion avec les infos utiles
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    // Route pour la déconnexion (gérée automatiquement par Symfony)
    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        // Ce code ne sera jamais exécuté !
        // La déconnexion est prise en charge par la configuration de sécurité (security.yaml)
        throw new \LogicException('Cette méthode peut être vide, elle sera interceptée par la configuration de sécurité.');
    }
}
