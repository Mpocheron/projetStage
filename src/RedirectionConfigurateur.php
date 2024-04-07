<?php

namespace App;

use SplFileInfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectionConfigurateur
{
    /**
     * Transforme la requête créée par le configurateur pour fournir le fichier correspondant
     * 
     * @param Request $request La requête http actuelle
     * @return string Le chemin du fichier demandé par le configurateur adapté à la structure des fichiers de la nouvelle app
     */
    public static function getLegacyScript(Request $request): string
    {
        // Récupération du chemin demandé par la requête
        $requestPathInfo = $request->getPathInfo();
        // Définition de l'emplacement des fichiers du configurateur
        $legacyRoot = __DIR__ . '/../configurateur';

        // Mapping du chemin de la requête vers les fichiers de l'appli
        if ($requestPathInfo == '/') {
            return "{$legacyRoot}/index.php";
        }
        // Modification du path des externes suite à la modification de la structure des fichiers après le passage sous git
        else if (str_contains($requestPathInfo, "/externe")) {
            return str_replace("/externe", __DIR__ . "/../code/extern", $requestPathInfo);
        }
        else {
            return $legacyRoot . $requestPathInfo;
        }

        throw new \Exception("Erreur de mapping pour $requestPathInfo");
    }

    /**
     * Execute les fichiers du configurateur et envoie une réponse http si nécessaire
     */
    public static function handleRequest(Request $request, Response $response, string $publicDirectory): void
    {
        $legacyScriptFilename = self::getLegacyScript($request);

        $fileType = (new SplFileInfo($legacyScriptFilename))->getExtension();
        // Si on a besoin d'un fichier php, on récupère l'objet session de Symfony pour qu'il soit utilisable dans les fichiers du configurateur
        if ($fileType == "php") {
            require $legacyScriptFilename;
        }
        // Sinon, on regarde le type de fichier pour récupérer le contenu et set le type de la réponse manuellement
        else {
            self::setHeaderInfo($response, $fileType, $legacyScriptFilename);

            $response->setContent(file_get_contents($legacyScriptFilename));
            $response->setStatusCode(Response::HTTP_OK);
            $response->prepare($request);
            $response->send();
        }
    }

    /**
     * Modifie le header de la réponse en fonction du type de fichier demandé
     */
    public static function setHeaderInfo(Response $response, string $fileType, string $legacyScriptFilename): void
    {
        switch ($fileType) {
            case "css":
                $response->headers->set('Content-Type', 'text/css');
                break;
            case "js":
                $response->headers->set('Content-Type', 'text/javascript');
                break;
            case "png":
                $response->headers->set('Content-Type', 'image/png');
                break;
            default:
            $response->headers->set('Content-Type', mime_content_type($legacyScriptFilename));
        }

        $response->headers->set('Content-Length', filesize($legacyScriptFilename));
    }
}