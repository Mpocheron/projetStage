<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

/**
 * Sert d'intermédiaire pour charger des services de Symfony, qui sont privés par défaut, depuis le configurateur
 * 
 * Cette classe est déclarée en tant que service publique dans config/services.yaml
 */
class ServiceLoader
{
    // Ici, on ne charge que le service logger, mais il est possible d'en ajouter d'autres si besoin
    public $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}