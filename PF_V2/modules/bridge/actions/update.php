<?php

include_once(dirname(__FILE__) . '/../logger.php'); // Inclure le fichier de journalisation

// Récupérer les paramètres nécessaires pour la mise à jour du stock
$product_id = Tools::getValue('product_id');
$quantity = Tools::getValue('quantity');

// Vérifier que l'ID du produit est fourni
if (!$product_id) {
    logMessage('Paramètre manquant : product_id est requis');
    die(json_encode(['error' => 'Paramètre manquant : product_id est requis']));
}

// Vérifier que la quantité est fournie
if ($quantity === null) {
    logMessage('Paramètre manquant : quantity est requis');
    die(json_encode(['error' => 'Paramètre manquant : quantity est requis']));
}

// Vérifier que l'ID du produit est un nombre entier valide et supérieur ou égal à 0
if (!ctype_digit($product_id) || (int)$product_id < 0) {
    logMessage('Paramètre invalide : product_id doit être un nombre entier positif ou zéro');
    die(json_encode(['error' => 'Paramètre invalide : product_id doit être un nombre entier positif ou zéro']));
}

// Vérifier que la quantité est un nombre valide et supérieur ou égal à 0
if (!is_numeric($quantity) || (float)$quantity < 0) {
    logMessage('Paramètre invalide : quantity doit être un nombre positif ou zéro');
    die(json_encode(['error' => 'Paramètre invalide : quantity doit être un nombre positif ou zéro']));
}

// Convertir la quantité en entier
$quantity = (int)$quantity;

/**
 * Fonction pour mettre à jour le stock d'un produit
 */
function updateStock($product_id, $quantity)
{
    // Charger le produit à partir de son ID
    $product = new Product((int)$product_id);

    // Vérifier si le produit existe
    if (!Validate::isLoadedObject($product)) {
        logMessage("Produit non trouvé : ID $product_id");
        die(json_encode(['error' => 'Produit non trouvé']));
    }

    // Mettre à jour le stock du produit
    try {
        StockAvailable::setQuantity((int)$product->id, 0, $quantity);
        logMessage("Stock mis à jour avec succès pour le produit ID $product_id, nouvelle quantité : $quantity");
        echo json_encode(['success' => 'Stock mis à jour avec succès']);
    } catch (Exception $e) {
        logMessage("Erreur lors de la mise à jour du stock pour le produit ID $product_id : " . $e->getMessage());
        die(json_encode(['error' => 'Erreur lors de la mise à jour du stock : ' . $e->getMessage()]));
    }
}

// Appeler la fonction pour exécuter la logique
updateStock($product_id, $quantity);
