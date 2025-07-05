<?php

/**
 * Classe BridgeLogger
 *
 * Cette classe utilitaire permet d'enregistrer des messages de log
 * dans un fichier dédié au module Bridge.
 * Elle offre une méthode statique pour écrire des logs avec un niveau de gravité.
 */
class BridgeLogger
{
    /**
     * Enregistre un message dans le fichier de log.
     *
     * @param string $message Le message à enregistrer.
     * @param string $level   Le niveau de gravité du message (ex : INFO, ERROR, DEBUG).
     */
    public static function log($message, $level = 'INFO')
    {
        // Définit le chemin du dossier de logs dans le module
        $logDir = _PS_MODULE_DIR_ . 'bridge/logs/';

        // Si le dossier logs n'existe pas, on le crée avec les permissions 0755
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Chemin complet vers le fichier de log
        $path = $logDir . 'module.log';

        // Récupère la date et l'heure actuelle au format ISO standardv
        $timestamp = date('Y-m-d H:i:s');

        // Construit la ligne de log avec la date, le niveau et le message
        $logLine = "[$timestamp][$level] $message\n";

        // Ajoute la ligne de log à la fin du fichier (créé s'il n'existe pas)
        file_put_contents($path, $logLine, FILE_APPEND);
    }
}
