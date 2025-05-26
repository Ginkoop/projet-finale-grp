<?php

/**
 * Fonction pour écrire des messages dans un fichier de logs.
 *
 * @param string $message Le message à enregistrer dans le fichier de logs.
 */
function logMessage($message)
{
    // Chemin vers le fichier de logs
    $log_file = dirname(__FILE__) . '/logs/module.log';

    // Assurez-vous que le répertoire des logs existe
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }

    // Obtenir la date et l'heure actuelles
    $timestamp = date("Y-m-d H:i:s");

    // Formater le message avec un horodatage
    $log_entry = "[$timestamp] $message" . PHP_EOL;

    // Écrire le message dans le fichier de logs
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}
