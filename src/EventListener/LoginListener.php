<?php

namespace App\EventListener;

use App\Entity\AppInfos;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class LoginListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Décide si l'utilisateur doit être connecté en tant que session readonly
     * 
     * L'événement LoginSuccessful se déclenche lorsqu'un utilisateur est connecté avec succès. 
     * Si un utilisateur est déjà connecté, celui en cours de connexion est mis en readonly, sinon il est connecté normalement et est stocké dans la bd userLogged
     */
    #[AsEventListener(event: LoginSuccessEvent::class)]
    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
        /** @var \App\Entity\User $user */
        $user = $event->getUser();
        $session = $this->setSessionAttributes($event, $user);

        // L'entityManagerInterface permet d'aller chercher un élément dans la base de données et de la rendre sous forme d'Entité
        $appInfos = $this->entityManager->getRepository(AppInfos::class)->findOneBy(['field' => 'userLogged']);
        $userLogged = $appInfos->getValue();
        
        $newUserInfo = $user->getNewUserInfo();
        $this->logger->debug("________newUserInfo = {user}__________", ['user' => $newUserInfo]);

        // Si userLogged n'est pas vide et que l'utilisateur qui se connecte est différent de celui déjà enregistré -> readonly
        if ($userLogged != "" && strcmp($userLogged, $newUserInfo) !== 0) {
            $session->set("readonly", true);
            $this->logger->debug("readonly = true");
        }
        // Sinon on le connecte normalement et on le sauvegarde dans la bd
        else {
            $this->logger->debug("readonly = false");
            $this->storeUserLogged($appInfos, $newUserInfo);
        }
    }

    /**
     * Permet de modifier la valeur de userLogged et dans sauvegarder le changement dans la bd
     * 
     * @param \App\Entity\AppInfos $appInfos Entité doctrine correspondant à la ligne userLogged de la table AppInfos
     * @param string $newUserInfo "firstname name clientIP" de l'utilisateur en cours de connexion
     */
    public function storeUserLogged(AppInfos $appInfos, string $newUserInfo): void
    {
        $appInfos->setValue($newUserInfo);
        // Applique les changements apporté à l'objet sur la base de données
        $this->entityManager->flush();
    }

    /**
     * Initialise les attributs de la session de l'utilisateur en fonction de ses données en bd
     * 
     * @param \Symfony\Component\Security\Http\Event\LoginSuccessEvent $event Evénement déclenché lorsqu'une authentification est finalisée
     * @param \App\Entity\User $user Objet contenant l'utilisateur qui vient de se connecter
     * 
     * @return Symfony\Component\HttpFoundation\Session\SessionInterface objet contenant la session liée à l'utilisateur connecté
     */
    public function setSessionAttributes(LoginSuccessEvent $event, User $user): SessionInterface
    {
        // L'objet session est uniquement accessible via Request
        $session = $event->getRequest()->getSession();

        $session->set('origin', 'configurateur');
        $session->set('username', $user->getUserIdentifier());
        $session->set('level', $user->getLevel());
        $session->set('da', $user->getDa());
        $session->set('readonly', false);

        return $session;
    }
}
