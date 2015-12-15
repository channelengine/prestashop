<?php

/**
 * 2007-2015 PrestaShop
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
 *  @copyright 2007-2015 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Channelengine extends Module {

    protected $config_form = false;

    public function __construct() {
        $this->name = 'channelengine';
        $this->tab = 'market_place';
        $this->version = '1.0.0';
        $this->author = 'Manish Gautam';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('ChannelEngine');
        $this->description = $this->l('ChannelEngine extension for Prestashop
');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the module?');

        $this->ps_versions_compliancy = array('min' => '1.4', 'max' => '1.7');
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install() {
        Configuration::updateValue('CHANNELENGINE_LIVE_MODE', false);

        return parent::install() &&
                $this->registerHook('header') &&
                $this->registerHook('backOfficeHeader') &&
                $this->registerHook('actionOrderStatusPostUpdate') &&
                $this->registerHook('actionOrderStatusUpdate') &&
                $this->registerHook('actionProductAdd') &&
                $this->registerHook('actionProductUpdate') &&
                $this->registerHook('orderConfirmation') &&
                $this->registerHook('displayHeader') &&
                $this->registerHook('displayTop');
    }

    public function uninstall() {
        Configuration::deleteByName('CHANNELENGINE_LIVE_MODE');
        Configuration::deleteByName('CHANNELENGINE_ACCOUNT_API_KEY');
        Configuration::deleteByName('CHANNELENGINE_ACCOUNT_API_SECRET');
        Configuration::deleteByName('CHANNELENGINE_ACCOUNT_NAME');
        Configuration::deleteByName('CHANNELENGINE_EXPECTED_SHIPPING_PERIOD');
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent() {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitChannelengineModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm() {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitChannelengineModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
                . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

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
    protected function getConfigForm() {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('ENABLE MODULE'),
                        'name' => 'CHANNELENGINE_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 6,
                        'type' => 'text',
//                        'prefix' => '<i class="icon icon-envelope"></i>',
//                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'CHANNELENGINE_ACCOUNT_API_KEY',
                        'label' => $this->l('Api Key'),
                    ),
                    array(
                        'col' => 6,
                        'type' => 'text',
//                        'prefix' => '<i class="icon icon-envelope"></i>',
//                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'CHANNELENGINE_ACCOUNT_API_SECRET',
                        'label' => $this->l('Api Secret'),
                    ),
                    array(
                        'col' => 6,
                        'type' => 'text',
//                        'prefix' => '<i class="icon icon-envelope"></i>',
//                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'CHANNELENGINE_ACCOUNT_NAME',
                        'label' => $this->l('Account Name'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
//                        'prefix' => '<i class="icon icon-envelope"></i>',
//                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'CHANNELENGINE_EXPECTED_SHIPPING_PERIOD',
                        'label' => $this->l('Expected Shipping Period for back orders'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues() {
        return array(
            'CHANNELENGINE_LIVE_MODE' => Configuration::get('CHANNELENGINE_LIVE_MODE', null),
            'CHANNELENGINE_ACCOUNT_API_KEY' => Configuration::get('CHANNELENGINE_ACCOUNT_API_KEY'),
            'CHANNELENGINE_ACCOUNT_API_SECRET' => Configuration::get('CHANNELENGINE_ACCOUNT_API_SECRET', null),
            'CHANNELENGINE_ACCOUNT_NAME' => Configuration::get('CHANNELENGINE_ACCOUNT_NAME', null),
            'CHANNELENGINE_EXPECTED_SHIPPING_PERIOD' => Configuration::get('CHANNELENGINE_EXPECTED_SHIPPING_PERIOD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess() {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader() {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader() {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    public function hookDisplayHeader() {
        if (!Configuration::get('CHANNELENGINE_LIVE_MODE')) {
            return ;
        } else {
            $script = "<script>
(function (T, r, i, t, a, c) {
T.ce = T.ce || function () { T.ce.eq = T.ce.eq || []; T.ce.eq.push(arguments); }, T.ce.url = t;
a = r.createElement(i); a.async = 1; a.src = t + '/content/scripts/ce.js';
c = r.getElementsByTagName(i)[0]; c.parentNode.insertBefore(a, c);
})(window, document, 'script', '//www.channelengine.net');
ce('set:account', '" . Configuration::get('CHANNELENGINE_ACCOUNT_NAME', null) . "');
ce('track:click');
</script>";
            return $script;
        }
//        return "HELLO THIS IS ANIL GAUTAM";
        /* Place your code here. */
    }

    public function hookDisplayTop() {
        /* Place your code here. */
    }

    /**
     * To track transactions
     */
    public function hookOrderConfirmation($params) {
        if (!Configuration::get('CHANNELENGINE_LIVE_MODE')) {
            return ;
        }
        $id_order = Tools::getValue('id_order');
        $order = new Order((int) $id_order);
        $total = $order->total_paid;
        $shippingCost = $order->total_shipping;
        $vat = $order->total_paid_tax_incl - $order->total_paid_tax_excl;
        $invoiceAddress = new Address((int) $order->id_address_invoice);
        $city = $invoiceAddress->city;
        $country = $invoiceAddress->country;
        $products = $order->getProducts();
        $country_obj = new Country((int) $invoiceAddress->id_country);
        $country_code = $country_obj->iso_code;
        $order_items_array_full = array();
        foreach ($products as $key => $items) {
            $order_items_array['name'] = $items['product_name'];
            $order_items_array['price'] = $items['product_price'];
            $order_items_array['quantity'] = $items['product_quantity'];
            $order_items_array['category'] = $this->getProductCategories($items['id_product']);
            $order_items_array['merchantProductNo'] = $items['product_reference'];
            array_push($order_items_array_full, $order_items_array);
        }
        $script = "<script>ce('track:order', {
                merchantOrderNo: '" . $id_order . "',
                total: " . $total . ",
                vat: " . $vat . ",
                shippingCost: " . $shippingCost . ",
                city: '" . $city . "',
                country: '" . $country_code . "',
                orderLines: " . json_encode($order_items_array_full) . "
                }); </script>";
        return $script;
    }

    /**
     * getProductCategories return an array of categories which this product belongs to
     *
     * @return array of categories
     */
    public static function getProductCategories($id_product = '') {
        $ret = array();
        if ($row = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
            SELECT `id_category` FROM `' . _DB_PREFIX_ . 'category_product`
            WHERE `id_product` = ' . (int) $id_product)
        )
            foreach ($row as $val) {
                $cat = new Category($val['id_category'], (int) 1);
                $ret[] = $cat->name;
            }
        $ret_text = implode(" >", $ret);
        return $ret_text;
    }

}

function pr($data) {
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}
