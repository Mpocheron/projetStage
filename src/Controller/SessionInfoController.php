<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Récupère les infos de session et les envoie sous forme de JSON en réponse
 * 
 * J'ai créé ce controller pour tester l'envoie d'info au client pour les récupérer dans les scripts js du configurateur
 */
class SessionInfoController extends AbstractController
{
    #[Route('/sessioninfo', name: 'app_session_info')]
    public function index(Request $request): JsonResponse
    {
        $session = $request->getSession();

        $sessionInfo = array(
            "origin" => $session->get("origin", ""),
            "username" => $session->get("username", ""),
            "level" => $session->get("level", 0),
            "da" => $session->get("da", 0),
            "readonly" => $session->get("readonly"),
        );

        return $this->json($sessionInfo);
    }
}
