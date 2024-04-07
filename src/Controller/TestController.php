<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\AppInfos;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller de test qui sert à afficher certaines infos
 */
class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function index(): Response
    {
        return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);
    }

    #[Route('/test/{id}', name: 'app_test_db')]
    public function testDb(EntityManagerInterface $entityManager, string $id): Response
    {
        $user = $entityManager->getRepository(User::class)->find($id);
        
        return $this->render('test/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/info/{id}', name: 'app_info')]
    public function testInfos(EntityManagerInterface $entityManager, string $id): Response
    {
        $AppInfos = $entityManager->getRepository(AppInfos::class)->find($id);

        return $this->render('test/infos.html.twig', [
            'AppInfos' => $AppInfos,
        ]);
    }
}
