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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Bridge extends Module
{
    public function __construct()
    {
        $this->name = 'bridge';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Antoine';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Bridge');
        $this->description = $this->l('Bridge pour PrestaShop');

        $this->ps_versions_compliancy = array('min' => '8.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install() &&
            Configuration::updateValue('STOCKMANAGER_API_KEY', '123456');
    }

    public function uninstall()
    {
        return parent::uninstall() && Configuration::deleteByName('STOCKMANAGER_API_KEY');
    }

    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit' . $this->name)) {
            $api_key = Tools::getValue('STOCKMANAGER_API_KEY');
            if (!$api_key || empty($api_key)) {
                $output .= $this->displayError($this->l('La clé API est requise.'));
            } else {
                Configuration::updateValue('STOCKMANAGER_API_KEY', $api_key);
                $output .= $this->displayConfirmation($this->l('Paramètres mis à jour'));
            }
        }

        $output .= $this->displayForm();
        $output .= $this->displayLogs();

        return $output;
    }


    public function displayForm()
    {
        // Générer le formulaire de configuration
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Configuration'),
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->l('Clé API'),
                        'name' => 'STOCKMANAGER_API_KEY',
                        'size' => 32,
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Sauvegarder'),
                    'class' => 'btn btn-default pull-right',
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->show_toolbar = false;
        $helper->toolbar_scroll = false;
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Sauvegarder'),
                'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            ],
        ];

        $helper->fields_value['STOCKMANAGER_API_KEY'] = Configuration::get('STOCKMANAGER_API_KEY');

        return $helper->generateForm([$form]);
    }

    public function getLogs()
    {
        // Lire et retourner les logs
        $log_file = dirname(__FILE__) . '/logs/module.log';
        if (file_exists($log_file)) {
            return file_get_contents($log_file);
        }
        return $this->l('Aucun log disponible.');
    }

    public function displayLogs()
    {
        $logs = $this->getLogs();
        return '<h3>' . $this->l('Logs des Requêtes') . '</h3><pre>' . htmlspecialchars($logs) . '</pre>';
    }
}
