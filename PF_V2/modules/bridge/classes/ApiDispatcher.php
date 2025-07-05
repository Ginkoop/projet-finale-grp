<?php

// Déclaration de la classe ApiDispatcher qui centralise les appels aux différentes actions API
class ApiDispatcher
{
    // Méthode statique pour traiter la requête en fonction de l'action et des paramètres fournis
    public static function handleRequest($action, $params)
    {
        // On utilise un switch pour déterminer quelle action exécuter
        switch ($action) {

            // Si l'action demandée est "getAllProducts", on appelle la méthode correspondante
            case 'getAllProducts':
                // Retourne la liste de tous les produits avec leurs quantités disponibles
                return ProductStockManager::getAllProducts();

                // Si l'action demandée est "update", on met à jour le stock d'un produit
            case 'update':
                // Récupère l'ID du produit depuis les paramètres ou 0 par défaut
                $idProduct = (int)($params['id_product'] ?? 0);

                // Récupère la nouvelle quantité depuis les paramètres ou 0 par défaut
                $qty = (int)($params['qty'] ?? 0);

                // Appelle la méthode pour mettre à jour le stock du produit
                $success = ProductStockManager::updateStock($idProduct, $qty);

                // Retourne un tableau avec le statut de la mise à jour
                return [
                    'success' => $success,        // true si la mise à jour a réussi
                    'id_product' => $idProduct,   // ID du produit concerné
                    'new_qty' => $qty             // Nouvelle quantité définie
                ];

                // Si l'action n'est pas reconnue, retourne une erreur
            default:
                return ['error' => 'Unknown action'];
        }
    }
}
