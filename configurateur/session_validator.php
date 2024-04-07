<?php

require_once(__DIR__ . "/../base/Utility.php");

// La variable $request provient de RedirectionConfigurateur.php, et permet de récupérer la session avec laquelle a été envoyée la requête
$session = $request->getSession();

// Pas d'utilisateur connecté -> on redirige vers la page de login
if ($session->get('level', 0) == 0) {
    Utility::logEvent("no user connected");
    header("Location: /config/login");
    exit();
}
else {
    $username = $session->get('username', "");
    $readonly = $session->get('readonly', true);
    Utility::logEvent("username = $username -- readonly : $readonly");
}

// Exemple d'utilisation d'un service de Symfony dans un fichier du configurateur (ici le logger)
// On récupère la variable kernel déclarée dans index.php de Symfony, qui permet d'accéder au container contenant les différents services initialisés par Symfony.
global $kernel;

// Le service ServiceLoader est public donc directement récupérable
$serviceLoader = $kernel->getContainer()->get('App\Service\ServiceLoader');
// Cela permet d'accéder au service logger de Symfony, qui est normalement un service privé
$logger = $serviceLoader->logger;

// On peut ensuite utiliser ce service normalement
$logger->debug("debug message");