<?php

namespace App\EventListener;

use App\Entity\AppInfos;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LogoutEvent;

final class LogoutListener
{
    public function __construct(
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Permet de supprimer l'utilisateur de la table userLogged lors de la déconnexion
     * 
     * Le LogoutEvent est déclenché juste avant que Symfony ne complète le processus de déconnexion, il permet d'accéder au token d'authentification de l'utilisateur afin
     * de le supprimer de la table.
     */
    #[AsEventListener(event: LogoutEvent::class)]
    public function onLogoutEvent(LogoutEvent $event): void
    {
        // On récupère le token d'authentification de l'utilisateur connecté
        $token = $event->getToken();

        // Si un token existe -> un utilisateur est connecté
        if ($token) {
            /** @var \App\Entity\User $user */
            $user = $token->getUser();

            $userInfo = $user->getNewUserInfo();
            // $this->logger->debug("_____ username = {username}", ['username' => $userInfo]);

            // Récupération de la ligne contenant userLogged
            $appInfos = $this->entityManager->getRepository(AppInfos::class)->findOneBy(['field' => 'userLogged']);

            // Si l'utilisateur qui se déconnecte correspond à celui qui est stocké dans la bd, on le supprime
            if (strcmp($userInfo, $appInfos->getValue()) === 0) {
                $appInfos->setValue("");
                $this->entityManager->flush();
            }
        }
    }
}
