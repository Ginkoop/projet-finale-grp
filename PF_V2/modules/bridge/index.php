<?php

/**
 * 2007-2025 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2025 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
include_once(dirname(__FILE__) . '/logger.php'); // Inclure le fichier de journalisation

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

// Récupérer la clé API à partir de la configuration
$api_key = Configuration::get('STOCKMANAGER_API_KEY');

// Vérifier la présence des paramètres nécessaires dans la requête
$action = Tools::getValue('action');
$api_key_provided = Tools::getValue('api_key');

// Vérifier que la clé API fournie est correcte pour autoriser l'accès
if ($api_key_provided !== $api_key) {
    logMessage('Accès non autorisé : clé API invalide');
    die(json_encode(['error' => 'Accès non autorisé : clé API invalide']));
}

if ($action) {
    // Chemin vers le fichier d'action correspondant
    $action_file = dirname(__FILE__) . '/actions/' . $action . '.php';

    // Vérifier si le fichier d'action existe
    if (file_exists($action_file)) {
        logMessage("Action demandée : $action");
        include($action_file);
    } else {
        logMessage("Action inconnue ou fichier introuvable : $action");
        die(json_encode(['error' => 'Action inconnue ou fichier introuvable']));
    }
} else {
    logMessage('Aucune action spécifiée');
    die(json_encode(['error' => 'Aucune action spécifiée']));
}
