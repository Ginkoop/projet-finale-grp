<?php

/**
 * ProductStockManager est une classe utilitaire pour gérer les stocks des produits.
 * Elle fournit des méthodes pour mettre à jour les stocks et récupérer la liste des produits avec leurs stocks.
 */
class ProductStockManager
{
    /**
     * Met à jour la quantité en stock pour un produit donné.
     *
     * @param int $idProduct L'identifiant du produit.
     * @param int $newQty La nouvelle quantité en stock.
     * @return bool Retourne vrai si la mise à jour a réussi, faux sinon.
     */
    public static function updateStock($idProduct, $newQty)
    {
        // Crée une instance du produit avec l'identifiant fourni
        $product = new Product($idProduct);

        // Vérifie si le produit est chargé correctement
        if (Validate::isLoadedObject($product)) {
            // Met à jour la quantité en stock pour le produit
            StockAvailable::setQuantity($product->id, 0, (int)$newQty);
            return true;
        }
        return false;
    }

    /**
     * Récupère tous les produits avec leurs noms et quantités en stock.
     *
     * @return array Un tableau de produits avec leurs identifiants, noms et quantités en stock.
     */
    public static function getAllProducts()
    {
        // Requête SQL pour récupérer les produits avec leurs noms et quantités en stock
        $sql = 'SELECT p.id_product, pl.name, sa.quantity
                FROM ' . _DB_PREFIX_ . 'product p
                LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON p.id_product = pl.id_product
                LEFT JOIN ' . _DB_PREFIX_ . 'stock_available sa ON p.id_product = sa.id_product
                WHERE pl.id_lang = ' . (int)Context::getContext()->language->id;

        // Exécute la requête et retourne les résultats
        return Db::getInstance()->executeS($sql);
    }
}
