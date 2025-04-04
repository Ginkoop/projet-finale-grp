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

class Personnalisation extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'personnalisation';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Cinnk';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('personnalisation');
        $this->description = $this->l('Module qui permet de personnaliser un produit depuis le front et recuperer le visuelle finale dans le back.');

        $this->confirmUninstall = $this->l('Vous etes sur de vouloir supprimer ce module magnifique?');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('PERSONNALISATION_CATEGORY_ID', 0) &&
        Configuration::updateValue('PERSONNALISATION_LIVE_MODE', true);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayProductAdditionalInfo') &&
            $this->registerHook('actionFrontControllerSetMedia') &&
            $this->registerHook('actionCartSave');
    }

    public function uninstall()
    {
        Configuration::deleteByName('PERSONNALISATION_CATEGORY_ID') &&
        Configuration::deleteByName('PERSONNALISATION_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitPersonnalisationModule')) == true) {
            $this->postProcess();
        }

        $this->pen_category_id = (int)Configuration::get('PERSONNALISATION_CATEGORY_ID');

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPersonnalisationModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $categories = Category::getCategories($this->context->language->id, false);
        $categories_options = [];
        foreach ($categories as $category) {
            foreach ($category as $cat) {
                $categories_options[] = [
                    'id' => $cat['id_category'],
                    'name' => $cat['name']
                ];
            }
        }

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Paramètres'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'select',
                        'label' => $this->l('Catégorie des stylos'),
                        'name' => 'PERSONNALISATION_CATEGORY_ID',
                        'required' => true,
                        'options' => [
                            'query' => Category::getCategories($this->context->language->id, false, false),
                            'id' => 'id_category',
                            'name' => 'name'
                        ],
                        'desc' => $this->l('Sélectionnez la catégorie.'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Activer la personnalisation'),
                        'name' => 'PERSONNALISATION_LIVE_MODE',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Activé')
                            ],
                            [
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Désactivé')
                            ]
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Enregistrer'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'PERSONNALISATION_PEN_CATEGORY_ID' => Configuration::get('PERSONNALISATION_CATEGORY_ID'),
            'PERSONNALISATION_LIVE_MODE' => Configuration::get('PERSONNALISATION_LIVE_MODE'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookActionFrontControllerSetMedia()
    {
        
    }

    public function hookDisplayProductAdditionalInfo()
    {
       
    }

    public function hookActionCartSave()
    {
        
    }

    protected function isProductInPenCategory()
    {
        
    }

    protected function saveCustomization()
    {
        
    }
}
