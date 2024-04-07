<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Event\AuthenticationTokenCreatedEvent;

final class UserCheckListener
{
    /**
     * Vérifie que l'utilisateur qui tente de se connecter est valide
     * 
     * Cette méthode est appelée lorsqu'un token d'authentification est créé, c'est à dire lorsqu'un user correspondant au username 
     * fourni est trouvé, mais avant que l'authentification ne soit complétée. Elle vérifie que l'utilisateur est bien valide 
     * (pas de mdp par défaut ou expiré, échec de cnx < 5)
     * 
     * @return AuthenticationException Si l'utilisateur n'est pas valide, l'authentification échoue
     */
    #[AsEventListener(event: AuthenticationTokenCreatedEvent::class)]
    public function onAuthenticationTokenCreatedEvent(AuthenticationTokenCreatedEvent $event): void
    {
        /** @var \App\Entity\User $user */
        $user = $event->getPassport()->getUser();
        if (!$user->isValid()) {
            // Créé une exception et interrompt le processus d'authentification
            throw new AuthenticationException("Utilisateur non valide");
        }
    }
}