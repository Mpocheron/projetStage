<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/** 
 * Controller de test
 */
class ConfigurateurController extends AbstractController
{
    #[Route('/configurateurtest', name: 'app_configurateur')]
    public function index(): Response
    {
        return $this->render('configurateur/index.html.twig', [
            'controller_name' => 'ConfigurateurController',
        ]);
    }
}
