# Migration du Configurateur sous Symfony

- [Migration du Configurateur sous Symfony](#migration-du-configurateur-sous-symfony)
  - [Objectif](#objectif)
  - [Versions utilisées](#versions-utilisées)
  - [Symfony](#symfony)
    - [Organisation des fichiers](#organisation-des-fichiers)
    - [Schéma de fonctionnement](#schéma-de-fonctionnement)
  - [Réalisation](#réalisation)
    - [Passage à PHP 8](#passage-à-php-8)
    - [Intégration du configurateur dans Symfony](#intégration-du-configurateur-dans-symfony)
    - [Migration de la gestion des utilisateurs et du login dans Symfony](#migration-de-la-gestion-des-utilisateurs-et-du-login-dans-symfony)
      - [Entité Doctrine et base de données](#entité-doctrine-et-base-de-données)
      - [Gestion de la connexion](#gestion-de-la-connexion)
      - [Modifications sur le configurateur](#modifications-sur-le-configurateur)
      - [Logout](#logout)
    - [Migration de la gestion des sessions](#migration-de-la-gestion-des-sessions)
  - [Evolutions possibles](#evolutions-possibles)
    - [Adapter d’autres fonctionnalités du configurateur sous Symfony](#adapter-dautres-fonctionnalités-du-configurateur-sous-symfony)
      - [Utiliser des services de Symfony dans les fichiers du configurateur](#utiliser-des-services-de-symfony-dans-les-fichiers-du-configurateur)
      - [Migrer des fonctionnalités dans Symfony](#migrer-des-fonctionnalités-dans-symfony)
    - [Adapter à l’authentification OIDC](#adapter-à-lauthentification-oidc)


## Objectif

Intégrer la gestion des utilisateurs du configurateur sous Symfony, puis la gestion des sessions PHP.

## Versions utilisées

PHP 8.2
Symfony 7.0

________________

## Symfony

### Organisation des fichiers

- `config` : Fichiers de configuration de Symfony et de ses différents services
- `configurateur` : Dossier contenant tous les fichiers du configurateur
- `migrations` : Contient des fichiers en lien avec la création de la base de donnée avec Doctrine
- `public` : `index.php` et `.htaccess` (le `.htaccess` est installé avec la commande `composer require symfony/apache-pack`)
- `src/Controller` : Contient les contrôleurs, qui servent à définir des routes pour Symfony. Les requêtes sont dirigées vers les contrôleurs qui renvoient une réponses, par exemple en affichant une page ou en renvoyant un json
- `src/Entity` : Regroupe les entités Doctrine, qui sont des objets mappés avec les tables de la base de données
- `src/Repository` : Chaque entité a un repository correspondant pour échanger avec la base de données
- `src/EventListener` : Les listeners sont appelés lorsque l’événement correspondant est déclenché par Symfony
- `src/Form` : Création des formulaires
- `Src/Services` : Contient des classes contenant la majeure partie de la logique de l'application

### Schéma de fonctionnement

Le point d’entrée de l’application est le `index.php` de Symfony. Les requêtes sont redirigées vers un contrôleur si la route correspond, ou vers `RedirectionConfigurateur` sinon.

`RedirectionConfigurateur` permet d’aller chercher les fichiers du configurateur pour les exécuter ou les envoyer en réponse.

Lorsque la requête est envoyée vers un contrôleur, elle peut déclencher des events ([doc gestion des events](https://symfony.com/doc/current/event_dispatcher.html)), ce qui va appeler les `EventListeners` pour effectuer diverses tâches liées à l'action en cours.

Les services peuvent être appelés n'importe où, en les déclarant dans le constructeur de la classe où on souhaite les utiliser ([doc sur l'utilisation des services](https://symfony.com/doc/current/service_container.html)).

---

## Réalisation

### Passage à PHP 8

`php/core/cache.php` : déclaration de la propriété file (l.23)

### Intégration du configurateur dans Symfony

J’ai créé un projet webapp Symfony, puis un dossier `configurateur` pour mettre tous les fichiers du configurateur dedans. J’ai modifié le `index.php` de Symfony pour rediriger vers le configurateur les requêtes qui ne sont pas gérées par les fonctionnalités implémentées avec Symfony. (code récupéré depuis la [doc Symfony sur l'intégration d'une application déjà existante](https://symfony.com/doc/current/migration.html#booting-symfony-in-a-front-controller))

Les requêtes non gérées sont envoyées vers  `src/RedirectionConfigurateur`, qui fait le mapping entre les chemins des requêtes et les fichiers du configurateur.
- Pour les fichiers php : on fait un `require` pour exécuter le code dans le contexte de Symfony.
- Pour les autres types : `file_get_content` pour récupérer le contenu du fichier demandé, modification du header de la réponse http pour avoir le bon type mime déclaré et on envoie une réponse http avec le contenu du fichier.

Il faut modifier les `require` dans tous les fichiers php pour utiliser des chemin relatifs, ainsi que les paths d’accès aux bases de données (`appli-config.php`, `data.php` et `install.php`).

### Migration de la gestion des utilisateurs et du login dans Symfony

#### Entité Doctrine et base de données

Il faut commencer par configurer la base de donnée dans le `.env`, puis la créer avec `php bin/console doctrine:database:create`.
La commande `php bin/console make:user` permet de créer une entité Doctrine qui intègre par défaut des options pour gérer la connexion. On peut ensuite lui ajouter d'autres propriétés. Ensuite on applique ces changements sur la bd avec `php bin/console make:migration` puis `hp bin/console doctrine:migrations:migrate`. ([doc Symfony sur Doctrine](https://symfony.com/doc/current/doctrine.html#installing-doctrine))

#### Gestion de la connexion

La commande `php bin/console make:security:form-login` créé un template de page d’authentification ([doc login](https://symfony.com/doc/current/security.html#authenticating-users)) ainsi que le `SecurityController`. Celui-ci permet de créer la route `/login`, ainsi que d’afficher la page contenant le formulaire de connexion.

Le `UserCheckListener` est appelé lorsqu’un event `AuthenticationTokenCreated` est déclenché, c’est-à-dire qu’un utilisateur est identifié mais que le processus de login n’est pas encore validé. L’event déclenché contient le passeport créé lors de l’authentification, ce qui permet d’accéder à l’utilisateur qui est en train de se connecter. On peut s’en servir pour faire des vérifications supplémentaire sur l’utilisateur (vérifier s’il est valide ou si son mdp n’est pas celui par défaut pour l'exemple du configurateur).

#### Modifications sur le configurateur

J’ai supprimé l’appel au `logger.js` dans le `Main.js`. Les infos d’utilisateurs sont déclarées de manière arbitraire pour les tests. J’ai créé un fichier de vérification de la session `php/session/session_validator.php` qui fait une redirection vers la page de connexion `/login` si la session ne contient pas d’utilisateur connecté.

#### Logout

De la même manière que pour le login, le `SecurityController` créé la route `/logout`. Les appels à `user-logout.php` sont remplacé par des appels à l’url `/config/logout` dans les fichiers javascript `Main.js` et `App.js`. La déconnexion est entièrement gérée par la logique interne de Symfony.

### Migration de la gestion des sessions

Symfony gère automatiquement la création et destruction de session utilisateur lors du login et logout, mais permet d’accéder à l’objet session pour y apporter les informations supplémentaires dont on a besoin. 
Lorsque la connexion d’un utilisateur est complètement validée, un event `OnLoginSuccess` est déclenché. Le `LoginListener` est appelé grâce à cet event, et permet de récupérer les infos de l’utilisateur pour les stocker dans l’objet session, qui est accessible depuis l’objet `Request` (ou `RequestStack`). Il vérifie également si l’utilisateur doit être connecté en readonly s’il y a déjà un utilisateur connecté sur le configurateur.

Chaque requête suivante passe par `RedirectionConfigurateur`, qui peut accéder à la session depuis l’objet `Request`. On peut inclure le `session_validator.php` au début de chaque fichiers php pour accéder à l’objet session récupéré, ce qui permet de vérifier si un utilisateur est connecté et de récupérer ses informations, et éventuellement d’émettre une erreur ou rediriger vers le login si personne n’est connecté.

---

## Evolutions possibles

### Adapter d’autres fonctionnalités du configurateur sous Symfony

#### Utiliser des services de Symfony dans les fichiers du configurateur

Le kernel de Symfony est déclaré en tant que variable globale. En y accédant depuis un fichier du configurateur, on peut accéder au Container, qui contient tous les services de Symfony. Cela permet d’importer ceux dont on a besoin pour les utiliser directement dans le code du configurateur.

#### Migrer des fonctionnalités dans Symfony

Il est possible de recoder une fonctionnalité dans Symfony, et de modifier les appels à ce fichier dans le configurateur par la nouvelle route Symfony qui correspond.

### Adapter à l’authentification OIDC

Le système d’authentification de Symfony fonctionne également avec des tokens d’authentification provenant d’API, il suffirait de récupérer le token d’OIDC à la place des infos récupérées par le formulaire de la page de connexion. ([doc utilisation des tokens d'accès](https://symfony.com/doc/current/security/access_token.html))
