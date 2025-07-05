<?php

/**
 * 2007-2025 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * Ce fichier source est soumis à la licence académique libre (AFL 3.0)
 * qui est incluse avec ce package dans le fichier LICENSE.txt.
 * Il est également disponible via le web à cette URL :
 * http://opensource.org/licenses/afl-3.0.php
 * Si vous n'avez pas reçu de copie de la licence et que vous ne pouvez pas
 * l'obtenir via le web, veuillez envoyer un email
 * à license@prestashop.com afin que nous puissions vous en envoyer une copie immédiatement.
 *
 * AVERTISSEMENT
 *
 * N'éditez pas ou n'ajoutez pas à ce fichier si vous souhaitez mettre à jour PrestaShop vers des versions plus récentes à l'avenir.
 * Si vous souhaitez personnaliser PrestaShop pour vos besoins, veuillez consulter http://www.prestashop.com pour plus d'informations.
 *
 * @author PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2025 PrestaShop SA
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

// Vérifie si PrestaShop est défini pour éviter l'accès direct au fichier
if (!defined('_PS_VERSION_')) {
    exit;
}

// Inclut les fichiers de classe nécessaires pour le module
require_once __DIR__ . '/classes/BridgeLogger.php';
require_once __DIR__ . '/classes/ProductStockManager.php';
require_once __DIR__ . '/classes/ApiDispatcher.php';

/**
 * Bridge est un module PrestaShop qui fournit une API pour gérer les stocks, les produits, etc.
 */
class Bridge extends Module
{
    /**
     * Constructeur du module Bridge.
     * Définit les propriétés de base du module.
     */
    public function __construct()
    {
        $this->name = 'bridge'; // Nom du module
        $this->tab = 'administration'; // Onglet dans lequel le module apparaît
        $this->version = '1.0.0'; // Version du module
        $this->author = 'Antoine'; // Auteur du module
        $this->need_instance = 0; // Indique si le module nécessite d'être instancié
        $this->bootstrap = true; // Utilise le thème Bootstrap

        parent::__construct();

        $this->displayName = $this->l('Bridge'); // Nom affiché du module
        $this->description = $this->l('Bridge API pour PrestaShop (stock, produits, etc.)'); // Description du module
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_]; // Versions de PrestaShop compatibles
    }

    /**
     * Installe le module et configure les hooks et les valeurs de configuration nécessaires.
     *
     * @return bool Retourne vrai si l'installation a réussi, faux sinon.
     */
    public function install()
    {
        return parent::install()
            && $this->registerHook('displayBackOfficeHeader') // Enregistre un hook pour l'en-tête du back-office
            && Configuration::updateValue('BRIDGE_API_KEY', Tools::passwdGen(32)); // Génère et enregistre une clé API
    }

    /**
     * Désinstalle le module et nettoie les valeurs de configuration.
     *
     * @return bool Retourne vrai si la désinstallation a réussi, faux sinon.
     */
    public function uninstall()
    {
        return parent::uninstall()
            && Configuration::deleteByName('BRIDGE_API_KEY'); // Supprime la clé API de la configuration
    }

    /**
     * Génère le contenu de la page de configuration du module.
     *
     * @return string Le contenu HTML à afficher.
     */
    public function getContent()
    {
        $output = '';

        // Vérifie si le formulaire a été soumis
        if (Tools::isSubmit('submit' . $this->name)) {
            $api_key = Tools::getValue('BRIDGE_API_KEY');

            // Valide que la clé API n'est pas vide
            if (!$api_key || empty($api_key)) {
                $output .= $this->displayError($this->l('La clé API est requise.'));
            } else {
                Configuration::updateValue('BRIDGE_API_KEY', $api_key);
                $output .= $this->displayConfirmation($this->l('Clé API mise à jour avec succès.'));
            }
        }

        // Affiche le formulaire de configuration et les logs
        $output .= $this->displayForm();
        $output .= $this->displayLogs();

        return $output;
    }

    /**
     * Affiche le formulaire de configuration du module.
     *
     * @return string Le formulaire HTML.
     */
    public function displayForm()
    {
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Configuration API'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Clé API'),
                        'name' => 'BRIDGE_API_KEY',
                        'size' => 40,
                        'required' => true,
                        'class' => 'form-control',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Sauvegarder'),
                    'class' => 'btn btn-primary pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => ['BRIDGE_API_KEY' => Configuration::get('BRIDGE_API_KEY')],
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$form]);
    }


    /**
     * Récupère les logs du module.
     *
     * @return string Le contenu des logs.
     */
    public function getLogs()
    {
        $log_file = _PS_MODULE_DIR_ . $this->name . '/logs/module.log';

        if (file_exists($log_file)) {
            return file_get_contents($log_file);
        }

        return $this->l('Aucun log disponible.');
    }

    /**
     * Affiche les logs des requêtes API.
     *
     * @return string Les logs formatés en HTML.
     */
    public function displayLogs()
    {
        $logs = $this->getLogs();
        return '
    <div class="panel">
        <div class="panel-heading">
            <h3 class="panel-title">' . $this->l('Logs des Requêtes API') . '</h3>
        </div>
        <div class="panel-body">
            <pre style="background:#f8f8f8;border:1px solid #ccc;padding:10px;max-height:300px;overflow:auto;border-radius:4px;">' . htmlspecialchars($logs) . '</pre>
        </div>
    </div>';
    }
}
