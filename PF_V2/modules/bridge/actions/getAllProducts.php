<?php

function getAllProducts()
{
    try {
        // Récupérer tous les produits
        $products = Product::getProducts(
            Context::getContext()->language->id,
            0,
            0,
            'name',
            'ASC'
        );

        if (empty($products)) {
            die(json_encode(['error' => 'Aucun produit trouvé']));
        }

        // Retourner les produits sous forme de JSON
        header('Content-Type: application/json');
        echo json_encode($products);
    } catch (Exception $e) {
        die(json_encode(['error' => 'Erreur lors de la récupération des produits : ' . $e->getMessage()]));
    }
}

// Appeler la fonction
getAllProducts();
