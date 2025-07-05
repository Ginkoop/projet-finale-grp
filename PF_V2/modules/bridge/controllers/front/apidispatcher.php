<?php

/**
 * BridgeApiDispatcherModuleFrontController est un contrôleur frontal pour le module Bridge.
 * Il gère les requêtes API entrantes, vérifie l'authentification et dispatch les actions appropriées.
 */
class BridgeApiDispatcherModuleFrontController extends ModuleFrontController
{
    /**
     * Initialise le contenu et gère la logique de dispatch des requêtes API.
     * Vérifie la clé API, traite la requête et retourne une réponse JSON.
     */
    public function initContent()
    {
        // Appelle la méthode initContent de la classe parente pour s'assurer que les initialisations nécessaires sont effectuées
        parent::initContent();

        // Récupère la clé API fournie dans la requête
        $apiKey = Tools::getValue('api_key');

        // Récupère la clé API configurée dans le système
        $configKey = Configuration::get('BRIDGE_API_KEY');

        // Vérifie si la clé API fournie correspond à la clé configurée
        if ($apiKey !== $configKey) {
            // Enregistre une tentative d'accès non autorisé
            BridgeLogger::log("Unauthorized access with API key: $apiKey", 'ERROR');

            // Définit le code de réponse HTTP à 403 (Interdit)
            http_response_code(403);

            // Termine le script et retourne une réponse JSON d'erreur
            die(json_encode(['error' => 'Unauthorized']));
        }

        // Récupère l'action à effectuer à partir des paramètres de la requête
        $action = Tools::getValue('action');

        // Traite la requête et obtient le résultat
        $result = ApiDispatcher::handleRequest($action, $_GET);

        // Enregistre l'action et le résultat dans les logs
        BridgeLogger::log("Action: $action | Result: " . json_encode($result));

        // Définit le type de contenu de la réponse à JSON
        header('Content-Type: application/json');

        // Termine le script et retourne le résultat sous forme de JSON
        die(json_encode($result));
    }
}
