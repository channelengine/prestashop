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

// Import the required namespaces
class Channelengine extends Module {

    protected $config_form = false;
    protected $apiConfig = false;

    public function __construct() {
        $this->name = 'channelengine';
        $this->tab = 'market_place';
        $this->version = '2.2.2';
        $this->author = 'ChannelEngine';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('ChannelEngine');
        $this->description = $this->l('ChannelEngine extension for Prestashop');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the module?');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.8');

    }

    /**
     * Due to conflict with httpguzzle in prestashop 1.7 (ps uses older version). Only load when needed.
      */
    private function loadVendorFiles()
    {
        require_once( dirname(__FILE__) . "/vendor/autoload.php");
    }

    private function getApiConfig() {
        if (!$this->apiConfig) {
            $this->apiConfig = \ChannelEngine\Merchant\ApiClient\Configuration::getDefaultConfiguration();
            $this->apiConfig->setHost('https://'.Configuration::get('CHANNELENGINE_ACCOUNT_NAME').'.channelengine.net/api');
            $this->apiConfig->setApiKey('apikey', Configuration::get('CHANNELENGINE_ACCOUNT_API_KEY', null));
        }
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install($installData = true) {

        if ($installData) {
            if (!$this->installDb()) {
                return false;
            }
        }

        if (!Configuration::haskey('CHANNELENGINE_LIVE_MODE')) {
            Configuration::updateValue('CHANNELENGINE_LIVE_MODE', false);
        }

        if (!Configuration::haskey('CHANNELENGINE_SYNC_LANG')) {
            $id_lang = Configuration::get('PS_LANG_DEFAULT');
            Configuration::updateValue('CHANNELENGINE_SYNC_LANG', $id_lang);
        }

        if (!Configuration::haskey('CHANNELENGINE_CARRIER_AUTO')) {
            Configuration::updateValue('CHANNELENGINE_CARRIER_AUTO', 0);
        }

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('orderConfirmation') &&
            $this->registerHook('displayHeader');
    }

    public function uninstall($removeData = true) {

        if ($removeData) {
            if (!$this->unInstallDb()) {
                return false;
            }
            Configuration::deleteByName('CHANNELENGINE_ACCOUNT_API_SECRET'); //deprecated in v2.0
            Configuration::deleteByName('CHANNELENGINE_LIVE_MODE');
            Configuration::deleteByName('CHANNELENGINE_ACCOUNT_API_KEY');
            Configuration::deleteByName('CHANNELENGINE_ACCOUNT_NAME');
            Configuration::deleteByName('CHANNELENGINE_EXPECTED_SHIPPING_PERIOD');
            Configuration::deleteByName('CHANNELENGINE_CARRIER_AUTO');
            Configuration::deleteByName('CHANNELENGINE_CARRIER');
            Configuration::deleteByName('CHANNELENGINE_SYNC_LANG');
        }

        return parent::uninstall();
    }

    public function reset()
    {
        if (!$this->uninstall(false)) {
            return false;
        }
        if (!$this->install(false)) {
            return false;
        }

        return true;
    }

    private function installDb()
    {
        $result = true;
        $sql = array();

        //id_channelengine_return = unique for return
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "orders` "
            ."ADD `id_channelengine_order` int(10) UNSIGNED NOT NULL, "
            ."ADD `id_channelengine_shipment` int(10) UNSIGNED NOT NULL, "
            ."ADD `id_channelengine_return` int(10) UNSIGNED NOT NULL";

        //id_channelengine_product deprecated in v2.0
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "order_detail` "
            ."ADD `id_channelengine_shipment` int(10) UNSIGNED NOT NULL, "
            ."ADD `id_channelengine_return` int(10) UNSIGNED NOT NULL";

        //new v2.0
        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "order_slip` "
            ."ADD `id_channelengine_return` int(10) UNSIGNED NOT NULL";


        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "order_return` "
            ."ADD `id_channelengine_return` int(10) UNSIGNED NOT NULL";

        foreach($sql as $script) {
            $result &= Db::getInstance()->execute($script, false);
        }
        return $result;
    }

    private function unInstallDb()
    {
        $result = true;
        $sql = array();

        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "order_detail` "
            ."DROP `id_channelengine_shipment`, "
            ."DROP `id_channelengine_return`";

        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "orders` "
            ."DROP `id_channelengine_order`, "
            ."DROP `id_channelengine_shipment`, "
            ."DROP `id_channelengine_return`";

        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "order_slip` "
            ."DROP `id_channelengine_return`";

        $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "order_return` "
            ."DROP `id_channelengine_return`";

        foreach($sql as $script) {
            $result &= Db::getInstance()->execute($script, false);
        }
        return $result;
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

        return $this->renderForm();
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
        $languageId = Context::getContext()->language->id;

        $form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                )
            )
        );

        if (!Configuration::get('PS_GUEST_CHECKOUT_ENABLED')){

            $form['form']['description'] = $this->l('Guest checkout is disabled. Please enable this in the order preferences.');
        }


        $form['form']['input'] = array(
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
                'required' => true,
                'type' => 'text',
                'name' => 'CHANNELENGINE_ACCOUNT_API_KEY',
                'label' => $this->l('Api Key'),
            ),
            array(
                'col' => 6,
                'required' => true,
                'type' => 'text',
                'name' => 'CHANNELENGINE_ACCOUNT_NAME',
                'label' => $this->l('Account Name'),
            ),
            array(
                'col' => 3,
                'type' => 'text',
                'name' => 'CHANNELENGINE_EXPECTED_SHIPPING_PERIOD',
                'label' => $this->l('Expected Shipping Period for back orders'),
            ),
            array(
                'required' => true,
                'type' => 'switch',
                'label' => $this->l('Auto select carrier'),
                'name' => 'CHANNELENGINE_CARRIER_AUTO',
                'is_bool' => true,
                'desc' => $this->l('Automatically select carrier with best price.'),
                'values' => array(
                    array(
                        'id' => 'carrier_auto_on',
                        'value' => true,
                        'label' => $this->l('Enabled')
                    ),
                    array(
                        'id' => 'carrier_auto_off',
                        'value' => false,
                        'label' => $this->l('Disabled')
                    )
                ),
            ),
            array(
                'col' => 6,
                'required' => true,
                'cast' => 'intval',
                'type' => 'select',
                'name' => 'CHANNELENGINE_CARRIER',
                'label' => $this->l('Carrier for new orders'),
                'desc' =>  $this->l('Carrier for new orders. Also used if automatic carrier selection fails.'),
                'options' => array(
                    'query' => Carrier::getCarriers($languageId, true, false, false, null, Carrier::ALL_CARRIERS),
                    'id' => 'id_carrier',
                    'name' => 'name',
                )
            ),
            array(
                'col' => 3,
                'required' => true,
                'cast' => 'intval',
                'type' => 'select',
                'name' => 'CHANNELENGINE_NEW_ORDER_STATE',
                'label' => $this->l('Status for new orders'),
                'options' => array(
                    'query' => $allOrderStates = OrderState::getOrderStates($languageId),
                    'id' => 'id_order_state',
                    'name' => 'name',
                )
            ),
            array(
                'col' => 3,
                'required' => true,
                'cast' => 'intval',
                'type' => 'select',
                'name' => 'CHANNELENGINE_SYNC_LANG',
                'label' => $this->l('Language'),
                'desc' => $this->l('Language for synchronisation'),
                'options' => array(
                    'query' => Language::getLanguages(false),
                    'id' => 'id_lang',
                    'name' => 'name',
                )
            ),
        );
        $form['form']['submit'] = array(
            'title' => $this->l('Save'),
        );

        return $form;
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues() {
        $newOrderState = Configuration::get('CHANNELENGINE_NEW_ORDER_STATE', null);
        if($newOrderState === false) $newOrderState = Configuration::get('PS_OS_PAYMENT');

        return array(
            'CHANNELENGINE_LIVE_MODE' => Configuration::get('CHANNELENGINE_LIVE_MODE', null),
            'CHANNELENGINE_ACCOUNT_API_KEY' => Configuration::get('CHANNELENGINE_ACCOUNT_API_KEY'),
            'CHANNELENGINE_ACCOUNT_NAME' => Configuration::get('CHANNELENGINE_ACCOUNT_NAME', null),
            'CHANNELENGINE_EXPECTED_SHIPPING_PERIOD' => Configuration::get('CHANNELENGINE_EXPECTED_SHIPPING_PERIOD', null),
            'CHANNELENGINE_CARRIER_AUTO' => Configuration::get('CHANNELENGINE_CARRIER_AUTO', null),
            'CHANNELENGINE_CARRIER' => Configuration::get('CHANNELENGINE_CARRIER', null),
            'CHANNELENGINE_NEW_ORDER_STATE' => $newOrderState,
            'CHANNELENGINE_SYNC_LANG' => Configuration::get('CHANNELENGINE_SYNC_LANG'),
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
            //            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }


    public function hookDisplayHeader() {
        if (!Configuration::get('CHANNELENGINE_LIVE_MODE')) {
            return;
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
    }

    /**
     * @return bool
     * get returns from channelengine and create order return in prestashop.
     */
    public function cronReturnSync() {
        $this->loadVendorFiles();

        $this->getApiConfig();
        //Retrieve returns
        $returnApi = new \ChannelEngine\Merchant\ApiClient\Api\ReturnApi(null, $this->apiConfig);

        $dateNow = date('Y-m-d H:i:s');
        $createdSince = Configuration::get('CHANNELENGINE_RETURNSYNC_LASTSYNC');
        if (!$createdSince) {
            $createdSince = date('Y-m-d H:i:s', strtotime('Now -1 year')); //Set a default timeframe like 1 year ago
        }

        try {
            $returns = $returnApi->returnGetDeclaredByChannel($createdSince);
        } catch (Exception $e) {
            $serverLog = print_r($e->getResponseObject(),true);
            $this->logMessage('cronReturnSync returnGetDeclaredByChannel: '. $serverLog . ' | ' . $e->getMessage(), 3 , $e->getCode());
            $this->pr($e->getMessage());
            return;
        }
        //Check declared returns
        if (is_null($returns) || $returns->getCount() == 0) {
            Configuration::updateValue('CHANNELENGINE_RETURNSYNC_LASTSYNC', $dateNow);
            return false;
        }

        foreach ($returns->getContent() as $return) {
            $return_lines = $return->getLines();
            $channelengine_order_id = $return->getMerchantOrderNo();
            $channelengine_return_id = (int)$return->getId();

            if ($channelengine_order_id == null) {
                continue; //this is probably a test order, not created from prestashop.
            }
            $prestashop_order_id = (int)$channelengine_order_id;

            //check order exists
            $order = new Order((int) $prestashop_order_id);
            if (!Validate::isLoadedObject($order)) {
                $this->logMessage('cronReturnSync - order not found: id_order='.$prestashop_order_id);
                continue;
            }

            //Check not yet processed -> change and process per detail line !!
            $returnExists = false;
            $sql = 'SELECT * FROM '. _DB_PREFIX_ . 'order_return WHERE id_channelengine_return = ' . $channelengine_return_id;
            $result = Db::getInstance('_PS_USE_SQL_SLAVE_')->getRow($sql);
            if ($result) {
                $returnExists = true;
            }
            if ($returnExists) {
                return;
            }

            //get all order_details. if $quantity_array not set, then not added in the order_return_detail.
            $sql = "SELECT id_order_detail FROM " . _DB_PREFIX_ . "order_detail  WHERE id_order ='" . $prestashop_order_id . "'";
            $ids_order_details = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            $ids_order_detail_array = array();
            foreach ($ids_order_details as $key => $ids_order_detail) {
                $ids_order_detail_array[$ids_order_detail['id_order_detail']] = $ids_order_detail['id_order_detail'];
            }
            $quantity_array = array();
            $keys = array_keys($ids_order_detail_array);

            foreach ($return_lines as $i => $return_line) {
                $quantity_array[$keys[$i]] = $return_line->getQuantity();
                $orderReturn = new OrderReturn();
                $orderReturn->id_customer = (int) $order->id_customer;
                $orderReturn->id_order = $prestashop_order_id;
                $orderReturn->question = $return->getCustomerComment();
            }
            $orderReturn->state = 1; //set status to: wait for confirmation

            $orderReturn->add();

            $customizationIds = array(); //customizations of order detail - no support for now.
            $customizationQtyInput = array();
            $orderReturn->addReturnDetail($ids_order_detail_array, $quantity_array, $customizationIds, $customizationQtyInput);

            $orderReturn->id_channelengine_return = $channelengine_return_id; //add id_channelengine_return to mark processed
            Db::getInstance()->update('order_return', array('id_channelengine_return' => $channelengine_return_id), 'id_order_return = ' . $orderReturn->id);
        }
        Configuration::updateValue('CHANNELENGINE_RETURNSYNC_LASTSYNC', $dateNow);
    }

    public function cronShipmentSync() {
        $this->loadVendorFiles();

        //get all orders with shipped status & not yet send to channelengine
        $orders = $this->getShippedOrders();

        if (!count($orders)) {return;}
        //process orders
        $this->putPrestaOrderShipmentToChannelEngine($orders);

    }

    /**
     * @return bool
     * Send credit info (orderslip) info to channelengine
     */
    public function cronCreditSync() {
        $this->loadVendorFiles();
        //get all orders with shipped status & not yet send to channelengine
        $credits = $this->getCreditOrders();

        if (!count($credits)) {return;}
        //process credits
        $this->putPrestaCreditsToChannelEngine($credits);
    }

    /**
     * To track transactions
     */
    public function hookOrderConfirmation($params) {
        if (!Configuration::get('CHANNELENGINE_LIVE_MODE')) {
            return;
        }
        $id_order = Tools::getValue('id_order');
        $order = new Order((int) $id_order);
        $total = $order->total_paid;
        $shippingCost = $order->total_shipping;
        $vat = $order->total_paid_tax_incl - $order->total_paid_tax_excl;
        $invoiceAddress = new Address((int) $order->id_address_invoice);
        $city = $invoiceAddress->city;
        $products = $order->getProducts();
        $country_obj = new Country((int) $invoiceAddress->id_country);
        $country_code = $country_obj->iso_code;
        //push products to channel
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
                $cat = new \Category($val['id_category'], (int) 1);
                $ret[] = $cat->name;
            }

        $ret_text = implode(" > ", $ret);
        return $ret_text;
    }

    private function getOffers($updatedSince = 0, $page = null, $productId = null) {
        $ctx = Context::getContext();

        $id_lang = (int)Configuration::get('CHANNELENGINE_SYNC_LANG');
        $id_shop = (int) $ctx->shop->id;

        $sql = 'SELECT p.*, product_shop.*, s.quantity ,'
            . '( '
            . '    SELECT t.rate '
            . '    FROM `' . _DB_PREFIX_ . 'tax` t '
            . '    INNER JOIN `' . _DB_PREFIX_ . 'tax_rule` tr ON (tr.id_tax = t.id_tax) '
            . '    INNER JOIN `' . _DB_PREFIX_ . 'country` c ON (tr.id_country = c.id_country) '
            . Shop::addSqlAssociation('country', 'c') . ' '
            . '    WHERE c.iso_code = \'NL\''
            . '    AND tr.id_tax_rules_group = p.id_tax_rules_group'
            . '    ORDER BY t.rate DESC '
            . '    LIMIT 1 '
            . ') rate '
            . 'FROM `' . _DB_PREFIX_ . 'product` p '
            . Shop::addSqlAssociation('product', 'p') . ' '
            . 'LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` s ON s.id_product = p.id_product AND s.id_product_attribute = 0 ';

        $sql .= 'WHERE ';

        if(!is_null($productId)) $sql .= ' p.id_product = ' . intval($productId) . ' AND ';

        $sql .= ' product_shop.visibility IN ("both", "catalog") '
            . 'AND p.date_upd >= \'' . date('Y-m-d H:i:s', $updatedSince) . '\'';

        if(!is_null($page)) {
            $page = intval($page);
            if($page <= 0) $page = 1;

            $limit = 1000;
            $offset = $limit * ($page - 1);
            $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }

        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);

        $rq = $db->executeS($sql);
        if(!$rq) var_dump($db->getMsgError());

        return $rq;
    }

    private function getProducts($updatedSince = 0, $page = null, $productId = null) {
        $ctx = Context::getContext();

        $id_lang = (int)Configuration::get('CHANNELENGINE_SYNC_LANG');
        $id_shop = (int) $ctx->shop->id;

        $sql = 'SELECT p.*, product_shop.*, pl.*, m.name AS manufacturer_name, s.quantity ,'
            . '( '
            . '    SELECT cp.id_category '
            . '    FROM `' . _DB_PREFIX_ . 'category_product` cp '
            . '    INNER JOIN `' . _DB_PREFIX_ . 'category` c ON (cp.id_category = c.id_category) '
            . Shop::addSqlAssociation('category', 'c') . ' '
            . '    WHERE cp.id_product = p.id_product '
            . '    ORDER BY c.level_depth DESC '
            . '    LIMIT 1 '
            . ') id_category, '
            . '( '
            . '    SELECT t.rate '
            . '    FROM `' . _DB_PREFIX_ . 'tax` t '
            . '    INNER JOIN `' . _DB_PREFIX_ . 'tax_rule` tr ON (tr.id_tax = t.id_tax) '
            . '    INNER JOIN `' . _DB_PREFIX_ . 'country` c ON (tr.id_country = c.id_country) '
            . Shop::addSqlAssociation('country', 'c') . ' '
            . '    WHERE c.iso_code = \'NL\''
            . '    AND tr.id_tax_rules_group = p.id_tax_rules_group'
            . '    ORDER BY t.rate DESC '
            . '    LIMIT 1 '
            . ') rate '
            . 'FROM `' . _DB_PREFIX_ . 'product` p '
            . Shop::addSqlAssociation('product', 'p') . ' '
            . 'LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.id_product = pl.id_product AND pl.id_shop = ' . $id_shop . ') '
            . 'LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` s ON s.id_product = p.id_product AND s.id_product_attribute = 0 '
            . 'LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.id_manufacturer = p.id_manufacturer) ';

        $sql .= 'WHERE ';

        if(!is_null($productId)) $sql .= ' p.id_product = ' . intval($productId) . ' AND ';

        $sql .= ' pl.id_lang = ' . $id_lang . ' '
            . 'AND product_shop.visibility IN ("both", "catalog") '
            . 'AND product_shop.active = 1 '
            . 'AND p.date_upd >= \'' . date('Y-m-d H:i:s', $updatedSince) . '\'';

        if(!is_null($page)) {
            $page = intval($page);
            if($page <= 0) $page = 1;

            $limit = 1000;
            $offset = $limit * ($page - 1);
            $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }

        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);

        $rq = $db->executeS($sql);
        if(!$rq) var_dump($db->getMsgError());

        return $rq;
    }

    public function getAttributeCombinations($id_product = FALSE) {

        $ctx = Context::getContext();

        $id_lang = (int)Configuration::get('CHANNELENGINE_SYNC_LANG');
        $id_shop = (int) $ctx->shop->id;

        if (!Combination::isFeatureActive()) {
            return array();
        }

        if ($id_product) {
            $condition = ' WHERE pa.id_product = ' . $id_product;
        } else {
            $condition = "";
        }
        $sql = 'SELECT pa.*, pac.`id_product_attribute`, product_attribute_shop.*, ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` AS group_name, al.`name` AS attribute_name, a.`id_attribute`,
                (
                    SELECT SUM(s.`quantity`)
                    FROM ' . _DB_PREFIX_ . 'stock_available s
                    WHERE s.id_shop = ' . $id_shop . '
                    AND s.id_product = pa.id_product
                    AND s.id_product_attribute = pa.id_product_attribute
                ) AS quantity,
                (
                SELECT pai.id_image
                FROM  `' . _DB_PREFIX_ . 'product_attribute_image` as pai
                WHERE pai.id_product_attribute = pa.id_product_attribute
                LIMIT 1
                ) AS id_image
                FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                ' . Shop::addSqlAssociation('product_attribute', 'pa') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . $id_lang . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) $id_lang . ')
                ' . $condition
            . ' ORDER BY pa.`id_product_attribute`';

        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        //Get quantity of each variations
        $attributes = array();
        if (!$res)
            return $attributes;

        foreach ($res as &$row) {
            $id = $row['id_product'];

            if (!isset($attributes[$id])) {
                $attributes[$id] = array();
            }

            //grouping in query is off. Only add product row once.
            $id_product_attribute = $row['id_product_attribute'];
            if (!isset($attributes[$id][$id_product_attribute])) {
                $attributes[$id][$id_product_attribute] = $row;
            }

            //add all combination info in array to the
            $id_attribute = $row['id_attribute'];
            $attributes[$id][$id_product_attribute]['all_attribute_info'][$id_attribute] = array(
                'id_attribute' => $row['id_attribute'],
                'id_attribute_group' => $row['id_attribute_group'],
                'is_color_group' => $row['is_color_group'],
                'group_name' => $row['group_name'],
                'attribute_name' => $row['attribute_name'],
            );

        }
        return $attributes;
    }

    private function getCategories() {
        $id_lang = Configuration::get('CHANNELENGINE_SYNC_LANG');
        $sql = 'SELECT c.`id_category`, c.`id_parent`, cl.`name` '
            . 'FROM `' . _DB_PREFIX_ . 'category` c '
            . Shop::addSqlAssociation('category', 'c') . ' '
            . 'LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (cl.`id_category` = c.`id_category` ' . Shop::addSqlRestrictionOnLang('cl') . ') '
            . 'WHERE cl.`id_lang` = ' . (int) $id_lang . ' '
            . 'AND c.`active` = 1 '
            . 'ORDER BY c.`level_depth` ASC';

        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $categories = array();

        if (!$rq)
            return $categories;

        foreach ($rq as &$row) {
            $id = $row['id_category'];
            $parentId = $row['id_parent'];
            $name = $row['name'];

            if (!empty($parentId) && isset($categories[$parentId])) {
                $name = $categories[$parentId] . ' > ' . $name;
            }

            $categories[$id] = $name;
        }

        return $categories;
    }

    /**
     * - Fetch and convert prestashop products to CE products
     */
    public function getChannelEngineProducts($lastUpdatedTimestamp, $page = null, $productId = null) {
        $prestaProducts = $this->getProducts($lastUpdatedTimestamp, $page, $productId);
        if (!count($prestaProducts)) {
            return array();
        }

        $categories = $this->getCategories();
        $extradata = $this->getExtraData($productId);
        $images = $this->getImages($productId);
        $combinations = $this->getAttributeCombinations($productId);
        $combinationImages = $this->getAttributeCombinationImages($productId);
        $products = [];

        foreach ($prestaProducts as $prestaProduct) {

            $id = $prestaProduct['id_product'];

            // Variations
            $product = $this->createProductObject($prestaProduct, null, $categories, $extradata, $images);
            $products[] = $product;

            if (isset($combinations[$id])) {
                $variants = $combinations[$id];
                foreach ($variants as $variant) {
                    $product = $this->createProductObject($prestaProduct, $variant, $categories, $extradata, $combinationImages);
                    $products[] = $product;
                }
            }
        }

        return $products;
    }

    public function pushProductsToChannelEngine($products) {

        try {
            $this->getApiConfig();
            $productApi = new \ChannelEngine\Merchant\ApiClient\Api\ProductApi(null, $this->apiConfig);
            $results = $productApi->productCreate($products);
            $this->pr($results);
        } catch (Exception $e) {
            $serverLog = print_r($e->getResponseObject(),true);
            $this->logMessage('cronProductSync getContent: '. $serverLog . ' | ' . $e->getMessage(), 3 , $e->getCode());
            $this->pr($e->getMessage());
        }
    }

    public function getChannelEngineOffers($lastUpdatedTimestamp, $page = null, $productId = null) {
        $offers = $this->getOffers($lastUpdatedTimestamp, $page, $productId);
        if (!count($offers)) {
            return array();
        }

        $combinations = $this->getAttributeCombinations($productId);
        $updates = [];

        foreach ($offers as $offer) {
            $id = $offer['id_product'];

            $update = $this->createOfferObject($offer, null);
            $updates[] = $update;

            // variations
            if (isset($combinations[$id])) {
                $variants = $combinations[$id];
                foreach ($variants as $variant) {
                    $update = $this->createOfferObject($offer, $variant);
                    $updates[] = $update;
                }
            }
        }

        return $updates;
    }

    public function pushOffersToChannelEngine($updates) {

        try {
            $this->getApiConfig();
            $offerApi = new \ChannelEngine\Merchant\ApiClient\Api\OfferApi(null, $this->apiConfig);
            $results = $offerApi->offerStockPriceUpdate($updates);
            $this->pr($results);
        } catch (Exception $e) {
            $serverLog = print_r($e->getResponseObject(), true);
            $this->logMessage('cronOfferSync getContent: '. $serverLog . ' | ' . $e->getMessage(), 3 , $e->getCode());
            $this->pr($e->getMessage());
        }
    }

    function createOfferObject($product, $variant) {
        $id = $product['id_product'];
        $offer = new \ChannelEngine\Merchant\ApiClient\Model\MerchantStockPriceUpdateRequest();

        $productVatRate = 0;
        $productVatRateType = $this->getVatRateType();
        if (isset($product['rate'])) {
            $productVatRate = floatval($product['rate']);
        }

        //>=15% is hoog  <15% is laag   <x% is super laag.
        $price = $product['price'] * (1.0 + ($productVatRate / 100.0));

        if (!$variant) {
            $offer->setMerchantProductNo($id);
            $offer->setPrice($price);
            $offer->setStock($product['quantity']);
        } else {
            //add variant price change to default price. Using product_attribute_shop.price
            if ($variant['price'] != 0) {
                $price = ($product['price'] + $variant['price']) * (1.0 + ($productVatRate / 100.0));
            }
            $offer->setParentMerchantProductNo($id);
            $offer->setMerchantProductNo($id . "-" . $variant['id_product_attribute']);
            $offer->setPrice($price);
            $offer->setStock($variant['quantity']);
        }

        if($offer->getStock() < 0) {
            $offer->setStock(0);
        }

        return $offer;
    }

    function createProductObject($prestaProduct, $variant, $categories, $extradata, $images) {
        $id = $prestaProduct['id_product'];
        $product = new \ChannelEngine\Merchant\ApiClient\Model\MerchantProductRequest();
        $product->setName($prestaProduct['name']);
        $product->setDescription(strip_tags($prestaProduct['description']));
        // Add short descriptions to the product feed
        $product['shortDescription'] = strip_tags($prestaProduct['description_short']);
        $product->setBrand($prestaProduct['manufacturer_name']);

        $productVatRate = 0;
        $productVatRateType = $this->getVatRateType(); //default rate.

        if(isset($extradata[$id])) {
            $this->setSpecs($product, $extradata[$id], false);
        }

        if (isset($prestaProduct['rate'])) {
            $productVatRate = floatval($prestaProduct['rate']);
            $productVatRateType = $this->getVatRateType($productVatRate);
        }
        $product->setVatRateType($productVatRateType);

        //>=15% is hoog  <15% is laag   <x% is super laag.
        $price = $prestaProduct['price'] * (1.0 + ($productVatRate / 100.0));

        $ed = $product->getExtraData();
        $minQty = new \ChannelEngine\Merchant\ApiClient\Model\ExtraDataItem();
        $minQty->setKey('MinimalOrderQuantity');
        $minQty->setIsPublic(false);

        $ed[] = $this->createExtraDataItem('Condition', $prestaProduct['condition']);
        $ed[] = $this->createExtraDataItem('Width', $prestaProduct['width']);
        $ed[] = $this->createExtraDataItem('Height', $prestaProduct['height']);
        $ed[] = $this->createExtraDataItem('Depth', $prestaProduct['depth']);
        $ed[] = $this->createExtraDataItem('Weight', $prestaProduct['weight']);
        $ed[] = $this->createExtraDataItem('Reference', $prestaProduct['reference']);

        if (!$variant) {
            $merchantProductNo = $id;
            $product->setStock($prestaProduct['quantity']);
            $product->setPurchasePrice(round($prestaProduct['wholesale_price'], 2));
            $product->setEan($this->extractGtin($prestaProduct));
            $minQty->setValue($prestaProduct['minimal_quantity']);
            $imageLookupId = $id;
        } else {
            $merchantProductNo = $id . "-" . $variant['id_product_attribute'];
            $product->setParentMerchantProductNo($id);
            $product->setStock($variant['quantity']);
            $product->setPurchasePrice(round($variant['wholesale_price'], 2));
            $product->setEan($this->extractGtin($variant));
            $minQty->setValue($variant['minimal_quantity']);
            $imageLookupId = $variant['id_product_attribute'];


            //add variant specific image
            if (isset($variant['id_image']) && $variant['id_image']) {
                $id_image = $variant['id_image'];
            }

            //add variant price change to default price. Using product_attribute_shop.price
            if ($variant['price'] != 0) {
                $price = ($prestaProduct['price'] + $variant['price']) * (1.0 + ($productVatRate / 100.0));
            }

            //loop all variant attribute_info
            if (isset($variant['all_attribute_info']) && is_array($variant['all_attribute_info'])) {
                $this->setSpecs($product, $variant['all_attribute_info'], true);
                //future setColor().
            }
        }

        $link = new Link();
        $proto = Configuration::get("PS_SSL_ENABLED") ? 'https://' : 'http://';
        if(isset($images[$imageLookupId])) {
            $i = 0;
            foreach($images[$imageLookupId] as $key => $image) {
                $id_image = $image['id_image'];
                $path =  $proto . $link->getImageLink($prestaProduct['link_rewrite'], $id_image, '');
                if($i == 0) {
                    $product->setImageUrl($path);
                } else {
                    $product['extraImageUrl' . $i] = $path;
                }
                $i++;
            }
        }

        $ed[] = $minQty;

        $product->setExtraData($ed);
        $product->setMerchantProductNo($merchantProductNo);
        $product->setPrice(round($price, 2));

        if (isset($prestaProduct['id_category'])) {
            $product->setCategoryTrail($categories[$prestaProduct['id_category']]);
        } else {
            $category_path = $this->getProductCategories($id);
            $product->setCategoryTrail($category_path);
        }

        $product->setShippingCost($prestaProduct['additional_shipping_cost']);
        $product->setUrl($link->getProductLink($id));

        if($product->getStock() < 0){
            $product->setStock(0);
        }

        return $product;
    }

    private function createExtraDataItem($key, $value) {
        $condition = new \ChannelEngine\Merchant\ApiClient\Model\ExtraDataItem();
        $condition->setKey($key);
        $condition->setIsPublic(false);
        $condition->setValue($value);

        return $condition;
    }

    private function setSpecs($product, $specs, $isVariant) {
        $nameKey = $isVariant ? 'group_name' : 'name';
        $valueKey = $isVariant ? 'attribute_name' : 'value';

        $ed = $product->getExtraData();

        foreach($specs as $spec) {
            $isColorGroup = (isset($spec['is_color_group']) && $spec['is_color_group'] == 1);

            $specName = $spec[$nameKey];
            $specValue = $spec[$valueKey];
            $specKey = strtolower($specName);

            // Filter some invalid values
            if(strtolower($specValue) == 'zie bovenstaand') continue;

            if($specKey == 'maat' || $specKey == 'afmetingen' || $specKey == 'size' || $specKey == 'dimensions') {
                $product->setSize($specValue);
            } elseif($isColorGroup || $specKey == 'kleur' || $specKey == 'color' || $specKey == 'colour') {
                $product->setColor($specValue);
            } else {
                $item = new \ChannelEngine\Merchant\ApiClient\Model\ExtraDataItem();
                $item->setKey($specName);
                $item->setValue($this->truncate($specValue, 100));
                $item->setIsPublic(true);
                $ed[] = $item;
            }
        }

        //todo: below code duplicate of above code ?
        $size = $product->getSize();
        if(empty($size)) {
            $specName = $spec[$nameKey];
            $specValue = $spec[$valueKey];
            $specKey = strtolower($specName);

            foreach($specs as $spec) {
                //future: add names of specKey config
                if(strpos($specKey, 'maat') !== false ||
                    strpos($specKey, 'afmetingen') !== false ||
                    strpos($specKey, 'size') !== false ||
                    strpos($specKey, 'dimensions') !== false) {
                    $product->setSize($specValue);
                    break;
                }
            }
        }

        $color = $product->getColor();
        if(empty($color)) {
            $specName = $spec[$nameKey];
            $specValue = $spec[$valueKey];
            $specKey = strtolower($specName);

            foreach($specs as $spec) {
                //future: add names of specKey config
                if(strpos($specKey, 'kleur') !== false ||
                    strpos($specKey, 'color') !== false ||
                    strpos($specKey, 'colour') !== false) {
                    $product->setColor($specValue);
                    break;
                }
            }
        }

        $product->setExtraData($ed);
    }

    private function extractGtin($product) {
        if (!empty($product['ean13']) && $this->validateGtin($product['ean13'])) {
            return $product['ean13'];
        } else if (!empty($product['isbn']) && $this->validateGtin($product['isbn'])) {
            return $product['isbn'];
        } else if (!empty($product['upc']) && $this->validateGtin($product['upc'])) {
            return $product['upc'];
        } else {
            return "00000000";
        }
    }

    private function getExtraData($productId = FALSE) {

        $id_lang = (int)Configuration::get('CHANNELENGINE_SYNC_LANG');
        $features = array();

        if (!Combination::isFeatureActive()) return $features;

        $sql = 'SELECT fl.name, fp.id_product, fvl.value '
            . 'FROM `' . _DB_PREFIX_ . 'feature_value_lang` fvl '
            . 'INNER JOIN `' . _DB_PREFIX_ . 'feature_product` fp ON fp.id_feature_value = fvl.id_feature_value '
            . 'INNER JOIN `' . _DB_PREFIX_ . 'feature_lang` fl ON fl.id_feature = fp.id_feature AND fl.id_lang = ' . $id_lang . ' '
            . 'INNER JOIN `' . _DB_PREFIX_ . 'feature` f ON fl.id_feature = f.id_feature '
            . Shop::addSqlAssociation('feature', 'f') . ' '
            . (($productId) ?  'WHERE fp.id_product = ' . (int)$productId . ' AND ': 'WHERE ')
            . 'fvl.id_lang = ' . $id_lang;
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        //Get quantity of each variations
        if (!$res) return $features;

        foreach ($res as &$row) {
            $id = $row['id_product'];

            if (!isset($features[$id])) {
                $features[$id] = array();
            }

            $features[$id][] = $row;
        }
        return $features;
    }

    private function getImages($productId = FALSE) {

        $ctx = Context::getContext();
        $id_lang = (int)Configuration::get('CHANNELENGINE_SYNC_LANG');
        $id_shop = (int) $ctx->shop->id;

        $images = array();

        $sql = 'SELECT i.id_image, i.id_product, i.position, i.cover '
            . 'FROM `' . _DB_PREFIX_ . 'image` i '
            . 'INNER JOIN `' . _DB_PREFIX_ . 'image_shop` i_s ON i_s.id_image = i.id_image AND i_s.id_shop = '  . $id_shop
            . (($productId) ?  'WHERE i.id_product = ' . (int)$productId : '') . ' '
            . 'ORDER BY i.id_product ASC, i.cover DESC, i.position ASC ';

        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if (!$res) return $images;

        foreach ($res as &$row) {
            $id = $row['id_product'];

            if (!isset($images[$id])) {
                $images[$id] = array();
            }

            $images[$id][] = $row;
        }
        return $images;
    }

    private function getAttributeCombinationImages($productId = FALSE) {

        $ctx = Context::getContext();
        $id_lang = (int)Configuration::get('CHANNELENGINE_SYNC_LANG');
        $id_shop = (int) $ctx->shop->id;

        $images = array();

        $sql = 'SELECT i.id_image, pai.id_product_attribute, i.position, i.cover '
            . 'FROM `' . _DB_PREFIX_ . 'product_attribute_image` pai '
            . 'INNER JOIN `' . _DB_PREFIX_ . 'image` i ON pai.id_image = i.id_image '
            . 'INNER JOIN `' . _DB_PREFIX_ . 'image_shop` i_s ON i_s.id_image = i.id_image AND i_s.id_shop = '  . $id_shop
            . (($productId) ?  'WHERE i.id_product = ' . (int)$productId : '') . ' '
            . 'ORDER BY pai.id_product_attribute ASC, i.cover DESC, i.position ASC ';

        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if (!$res) return $images;

        foreach ($res as &$row) {
            $id = $row['id_product_attribute'];

            if (!isset($images[$id])) {
                $images[$id] = array();
            }

            $images[$id][] = $row;
        }
        return $images;
    }

    private function truncate($string, $length) {
        if(strlen($string) <= $length) return $string;
        return substr($string, 0 , $length);
    }

    private function validateGtin($barcode) {
        // check to see if barcode is 13 digits long
        if (!preg_match("/^[0-9]+$/", $barcode)) return false;


        $length = strlen($barcode);
        if($length !== 8 && $length !== 12 && $length !== 13 && $length !== 14) return false;

        $barcode = str_pad($barcode, 14, "0", STR_PAD_LEFT);

        // Use total length excluding the check digit (13)
        $sum = 0;
        for($i = 0; $i < 13; $i++) $sum += intval($barcode[$i]) * (($i % 2 == 0) ? 3 : 1);
        $checkDigit = (10 - ($sum % 10)) % 10;

        return $checkDigit == intval($barcode[13]);
    }

    function cronOrdersSync() {
        $this->loadVendorFiles();

        if(!Configuration::get('PS_GUEST_CHECKOUT_ENABLED')) {
            $this->logMessage('cronOrdersSync - Guest checkout is disabled, please enable guest checkout');
            return false;
        }

        $orders = [];
        $funcOriginal = 'Original'; //use values from original currency. Set to false to get converted to EUR currency.
        $this->getApiConfig();
        $orderApi = new \ChannelEngine\Merchant\ApiClient\Api\OrderApi(null, $this->apiConfig);
        try {
            $orders = $orderApi->orderGetNew()->getContent();
        } catch (Exception $e) {
            $serverLog = print_r($e->getResponseObject(),true);
            $this->logMessage('cronOrdersSync getContent: '. $serverLog . ' | ' . $e->getMessage(), 3 , $e->getCode());
            $this->pr($e->getMessage());
            return;
        }


        $context = Context::getContext();

        foreach ($orders as $order) {
            try {
                $channelOrderId = $order->getId();
                $channelPaymentMethod = 'ChannelEngine Payment'; //$order->getPaymentMethod(); not always set

                //Check if order exists with this $channelOrderId.
                $orderExists = false;
                $sql = 'SELECT * FROM '. _DB_PREFIX_ . 'orders WHERE id_channelengine_order = ' . (int)$channelOrderId;
                $result = Db::getInstance('_PS_USE_SQL_SLAVE_')->getRow($sql);
                if ($result) {
                    //order already exists.
                    $orderExists = true;
                    $order_object_id = $result['id_order'];
                }

                if (!$orderExists) {

                    $currencyCode = $order->getCurrencyCode();
                    if (!Currency::exists($currencyCode,'')) {
                        //prestashop has no config defined for this currency
                        if (Currency::exists('EUR','')) {
                            //use currency converted to euro by channelengine.
                            $currencyCode = 'EUR';
                            $funcOriginal = '';
                        } else {
                            $this->logMessage('Error: currency does not exist in Prestashop: '.$currencyCode. ' for order: ' . $channelOrderId);
                            continue; //next order
                        }
                    }

                    $id_currency = Currency::getIdByIsoCode($currencyCode); //check if exists?
                    $customer = $this->createPrestaShopCustomer($order->getBillingAddress(), $order->getEmail());
                    if (!Validate::isLoadedObject($customer)) {
                        $this->logMessage('cronOrdersSync - Error create customer for order: '.$channelOrderId);
                        continue;
                    }

                    $id_customer = $customer->id;
                    $billingAddress = $this->createPrestaShopAddress($id_customer, $order->getBillingAddress(), $order);
                    $shippingAddress = $this->createPrestaShopAddress($id_customer, $order->getShippingAddress(), $order);

                    $lines = $order->getLines();

                    $id_lang = (int)Configuration::get('CHANNELENGINE_SYNC_LANG');
                    $id_cart = 0;

                    //create cart to get carrier. Only if auto carrier is set in module.
                    if (Configuration::get('CHANNELENGINE_CARRIER_AUTO') == '1') {
                        $cart = new Cart();
                        $cart->id_customer = $customer->id;
                        $cart->id_currency = $id_currency;
                        $cart->id_lang = $customer->id_lang;
                        $cart->id_address_delivery = $shippingAddress->id;
                        $cart->id_address_invoice = $billingAddress->id;
                        $cart->recyclable = 0;
                        $cart->gift	= 0;
                        $cart->secure_key = $customer->secure_key;

                        $cart->id_guest = 0 ;
                        $cart->id_shop = $context->shop->id;
                        $cart->id_shop_group = $context->shop->id_shop_group;

                        $cart->id_carrier = 0;
                        $cart->delivery_option = ''; //leave empty for getDeliveryOption()

                        $cart->add();
                        $id_cart = $cart->id;

                        if (!empty($lines)) {
                            $addressId = $cart->id_address_delivery;
                            foreach ($lines as $item) {
                                $this->createCartDetail($item, $cart->id, $addressId, $funcOriginal);
                            }
                        }

                        $default_country = new Country((int)$shippingAddress->id_country);
                        $delivery_option = $cart->getDeliveryOption($default_country, false, false);
                        //get id_carrier from carrier with best price
                        $delivery_option_list = $cart->getDeliveryOptionList($default_country);
                        foreach ($delivery_option as $id_address => $key) {
                            if (isset($delivery_option_list[$id_address][$key])) {
                                $carrierList = $delivery_option_list[$id_address][$key]['carrier_list'];
                                foreach ($carrierList as $key => $values) {
                                    $id_carrier = $key;
                                    continue; //only process first
                                }
                            }
                        }
                        //end create cart
                    }

                    //get hardcoded carrier from config OR if not automatically found by cart.
                    if (Configuration::get('CHANNELENGINE_CARRIER_AUTO') != '1' || !isset($id_carrier) || !$id_carrier) {
                        $id_carrier = $this->getCarrierId(); //from config.
                    }
                    $carrier = new Carrier($id_carrier);
                    if (!Validate::isLoadedObject($carrier)) {
                        $this->logMessage('cronOrdersSync - Carrier not found. Check module configuration.');
                        return false;
                    }

                    $order_object = new Order();
                    $order_object->id_address_delivery = $shippingAddress->id;
                    $order_object->id_address_invoice = $billingAddress->id;
                    $order_object->reference = 'ce-' . $channelOrderId;
                    $order_object->id_cart = $id_cart;
                    $order_object->id_currency = $id_currency;
                    $order_object->id_customer = $id_customer;
                    $order_object->id_carrier = $id_carrier;
                    $order_object->payment = $channelPaymentMethod; // "ChannelEngine Order";
                    $order_object->module = "channelengine";
                    $order_object->valid = 1;

                    $order_object->total_paid_tax_excl = (float)$order->{'get'.$funcOriginal.'TotalInclVat'}() - (float)$order->{'get'.$funcOriginal.'TotalVat'}();
                    $order_object->total_paid_tax_incl = (float)$order->{'get'.$funcOriginal.'TotalInclVat'}();

                    $order_object->total_paid = $order_object->total_paid_tax_incl;
                    $order_object->total_paid_real = 0; // set by addOrderPayment.

                    $order_object->total_products = (float)$order->{'get'.$funcOriginal.'SubTotalInclVat'}() - (float)$order->{'get'.$funcOriginal.'SubTotalVat'}();
                    $order_object->total_products_wt = (float)$order->{'get'.$funcOriginal.'SubTotalInclVat'}();

                    $discount = 0;
                    $order_object->total_discounts_tax_excl = $discount;
                    $order_object->total_discounts_tax_incl = $discount;
                    $order_object->total_discounts = $order_object->total_discounts_tax_incl;

                    $order_object->total_shipping_tax_incl = (float)$order->{'get'.$funcOriginal.'ShippingCostsInclVat'}();
                    $order_object->total_shipping_tax_excl = (float)$order->{'get'.$funcOriginal.'ShippingCostsInclVat'}() - (float)$order->{'get'.$funcOriginal.'ShippingCostsVat'}();

                    $carrierVat = $carrier->getTaxesRate($shippingAddress);
                    if (is_numeric($carrierVat)) {
                        $order_object->carrier_tax_rate = $carrierVat;
                        $precision = (Currency::getCurrencyInstance((int)$order_object->id_currency)->decimals * _PS_PRICE_DISPLAY_PRECISION_);
                        $order_object->total_shipping_tax_excl = Tools::ps_round($order_object->total_shipping_tax_incl / ( 1 + ($carrierVat/100)), $precision);
                    }

                    $order_object->total_shipping = $order_object->total_shipping_tax_incl;

                    $order_object->conversion_rate = 1;
                    $order_object->id_shop = $context->shop->id;
                    $order_object->id_shop_group = $context->shop->id_shop_group;
                    $order_object->id_lang = $id_lang;
                    $order_object->secure_key = md5(uniqid(rand(), true));
                    $order_object->add();

                    if (!Validate::isLoadedObject($order_object)) {
                        $this->logMessage('cronOrdersSync - Error create order for order: '.$channelOrderId);
                        continue;
                    }
                    $order_object_id = $order_object->id;

                    //new 2018-01-15 add order_payment. Before orderhistory to prevent reset of current_state
                    $transaction_id = null;
                    $order_object->addOrderPayment($order_object->total_paid_tax_incl, $channelPaymentMethod, $transaction_id);


                    // Insert new Order detail list using cart for the current order
                    $id_order_state = Configuration::get('CHANNELENGINE_NEW_ORDER_STATE');
                    if (!empty($lines)) {
                        $addressId = $order_object->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
                        foreach ($lines as $item) {
                            $this->createOrderDetail($item, $order_object_id, $addressId, $funcOriginal);
                        }
                    }

                    // Adding an entry in order_carrier table
                    $order_carrier = new OrderCarrier();
                    $order_carrier->id_order = (int) $order_object->id;

                    $order_carrier->id_carrier = (int) $id_carrier;
                    $order_carrier->weight = (float) $order_object->getTotalWeight();
                    $order_carrier->shipping_cost_tax_excl = (float) $order_object->total_shipping_tax_excl;
                    $order_carrier->shipping_cost_tax_incl = (float) $order_object->total_shipping_tax_incl;
                    $order_carrier->add();

                    //Add the order status history. Does NOT send out emails.
                    $new_history = new OrderHistory();
                    $new_history->id_order = (int)$order_object->id;
                    $new_history->id_order_state = (int)$id_order_state;
                    $new_history->add(); //updates the order !

                    //reload order_object to get current_state changes
                    $order_object = new Order($order_object_id);

                    //create invoice / deliveryslip
                    $new_os = new OrderState((int)$id_order_state, $order_object->id_lang);
                    if ($new_os->invoice && !$order_object->invoice_number) {
                        $use_existing_payment = false;
                        $order_object->setInvoice($use_existing_payment);
                    } elseif ($new_os->delivery && !$order->delivery_number) {
                        $order_object->setDeliverySlip();
                    }

                    //set channelengine order info in order.
                    Db::getInstance()->update('orders', array(
                        'id_channelengine_order' => $channelOrderId,
                        'channelengine_channel_order_no' => $order->getChannelOrderNo(),
                        'channelengine_channel_name' => $order->getChannelName()
                    ), 'id_order = ' . $order_object->id);

                    $message = "ChannelEngine Order: #" . $channelOrderId . "\nChannel Name: " . $order->getChannelName() . "\nChannel Order No: " . $order->getChannelOrderNo();

                    Db::getInstance()->execute("INSERT INTO " . _DB_PREFIX_ . "message (`id_cart`, `id_customer`, `id_employee`, `id_order`, `message`, `private`, `date_add`) VALUES (0, 0, 0, $order_object->id, '$message', 1, NOW())");
                }

                //Send confirmation to channelengine: order is created.
                $modelData = array(
                    'merchantOrderNo' => $order_object_id,
                    'orderId' => $channelOrderId
                );
                $orderAcknowledgementModel = new \ChannelEngine\Merchant\ApiClient\Model\OrderAcknowledgement($modelData);

                try {
                    $result = $orderApi->orderAcknowledge($orderAcknowledgementModel);
                } catch (Exception $e) {
                    $serverLog = print_r($e->getResponseObject(), true);
                    $this->logMessage('cronOrdersSync OrderAcknowledgement: '. $serverLog . ' | ' . $e->getMessage(), 3, $e->getCode());
                    $this->pr($e->getMessage());
                    return;
                }

                //trigger hook (normally triggered in: public function changeIdOrderState)
                Hook::exec('actionOrderStatusUpdate', array('newOrderStatus' => $new_os, 'id_order' => (int)$order_object->id), null, false, true, false, $order_object->id_shop);
            }
            catch(Exception $e)
            {
                $this->logMessage('Failed to save ChannelEngine order. ' . $e->getMessage(), 3, $e->getCode());
            }
        }
    }

    function createPrestaShopAddress($customerId, $ceAddress, $ceOrder) {
        $address = new Address();

        $address->firstname = $ceAddress->getFirstName();
        $address->lastname = $ceAddress->getLastName();
        $address->phone = $ceOrder->getPhone();

        $address->address1 = trim($ceAddress->getStreetName() . " " . $ceAddress->getHouseNr() . " " . $ceAddress->getHouseNrAddition());
        $address->postcode = $ceAddress->getZipCode();
        $address->city = $ceAddress->getCity();

        $address->id_customer = $customerId;
        $address->id_country = \Country::getByIso($ceAddress->getCountryIso());

        $address->company = $ceAddress->getCompanyName();
        $address->alias = ($ceAddress->getCompanyName() != "") ? "Company" : "Home";

        $address->add();

        return $address;
    }

    /**
     * Create a prestashop Customer
     * @param type $billingAddress
     * @param type $email
     * @return type
     */
    function createPrestaShopCustomer($billingAddress, $email) {
        $customer_object = new Customer();
        $customer_object->firstname = $billingAddress->getfirstName();
        $customer_object->lastname = $billingAddress->getLastName();
        $customer_object->email = $email;
        $customer_object->is_guest = 1;
        $customer_object->passwd = md5(uniqid(rand(), true));
        $customer_object->add();
        return $customer_object;
    }

    private function cleanTag($tag) {
        $tag = preg_replace("/[^A-Za-z0-9]/", '_', $tag);
        if(is_numeric(substr($tag, 0, 1))) $tag = '_' . $tag;
        return $tag;
    }
    /*
    * Function to handle request
    */

    function handleRequest() {
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        switch ($type) {
            case 'orders':
                $this->cronOrdersSync();
                $this->cronShipmentSync();
                break;
            case 'returns':
                $this->cronReturnSync();
                break;
            case 'products':
                $this->loadVendorFiles();
                $timestamp = isset($_GET['updatedSince']) ? $_GET['updatedSince'] : null;
                $page = isset($_GET['page']) ? $_GET['page'] : null;
                $products = $this->getChannelEngineProducts($timestamp, $page);
                $this->pushProductsToChannelEngine($products);
                break;
            case 'offers':
                $this->loadVendorFiles();
                $timestamp = isset($_GET['updatedSince']) ? $_GET['updatedSince'] : null;
                $page = isset($_GET['page']) ? $_GET['page'] : null;
                $products = $this->getChannelEngineOffers($timestamp, $page);
                $this->pushOffersToChannelEngine($products);
                break;
            case 'feed':
                $this->renderFeed();
                break;
            default:
                die('Unknown callback type');
        }
    }

    private function renderFeed() {
        $this->loadVendorFiles();
        require_once( dirname(__FILE__) . "/classes/SimpleXmlExtended.php");

        $products = $this->getChannelEngineProducts(0);
        $xml = new SimpleXMLExtended('<Products Version="' . $this->version . '" GeneratedAt="' . date('c') . '"></Products>');

        foreach($products as $product) {
            $pXml = $xml->addChild('Product');
            $getters = $product->getters();
            foreach($getters as $field => $getter) {
                $fieldValue = $product[$field];
                $field = ucfirst($field);
                if($field == 'ExtraData') {
                    $aXml = $pXml->addChild($field);
                    foreach($fieldValue as $ed) {
                        $aXml->addChildCData($this->cleanTag($ed->getKey()), $ed->getValue());
                    }
                } else {
                    $pXml->addChildCData($field, $fieldValue);
                }
            }
            $pXml->addChildCData('ShortDescription', $product['shortDescription']);
        }

        if(ob_get_length()) ob_clean();
        header('Content-Type: text/xml');
        echo($xml->asXML());
    }

    /**
     * Get VatRateType by vat rate.
     * @param int $productVatRate
     * @return string
     */
    private function getVatRateType($productVatRate = null) {
        $type = \ChannelEngine\Merchant\ApiClient\Model\MerchantProductRequest::VAT_RATE_TYPE_STANDARD;
        if ($productVatRate == null) {
            return $type;
        }

        if (floatval($productVatRate) < 5) {
            $type = \ChannelEngine\Merchant\ApiClient\Model\MerchantProductRequest::VAT_RATE_TYPE_SUPER_REDUCED;
        } elseif (floatval($productVatRate) < 15) {
            $type = \ChannelEngine\Merchant\ApiClient\Model\MerchantProductRequest::VAT_RATE_TYPE_REDUCED;
        }
        return $type;
    }

    /**
     * @return array
     * Get all shipped orders not yet reported to ChannelEngine
     */
    private function getShippedOrders() {
        $orders = array();

        $query = 'SELECT id_order FROM ' . _DB_PREFIX_ . 'orders WHERE id_channelengine_order > 0 AND id_channelengine_shipment = 0';

        $result = Db::getInstance('_PS_USE_SQL_SLAVE_')->executeS($query);
        foreach($result as $result) {
            $order = new Order($result['id_order']);
            if (Validate::isLoadedObject($order)) {
                if ($order->hasBeenShipped()) {
                    $orders[] = $order;
                }
            }
        }
        return $orders;
    }

    private function putPrestaOrderShipmentToChannelEngine($orders)
    {
        $this->getApiConfig();
        $shipmentApi = new \ChannelEngine\Merchant\ApiClient\Api\ShipmentApi(null, $this->apiConfig);

        foreach($orders as $order) {
            try {
                // Marking orders as shipped, partially shipped or not shipped is done
                // by posting shipments.
                // Note: Use data from an existing order here
                //                $id_order = Tools::getValue('id_order');
                $id_order = $order->id;
                //                $sql = 'SELECT id_channelengine_order FROM ' . _DB_PREFIX_ . 'orders WHERE id_order =' . $id_order;
                $sql = 'SELECT id_channelengine_order, id_channelengine_shipment FROM ' . _DB_PREFIX_ . 'orders WHERE id_order =' . $id_order;
                $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
                $channelengine_order_id = $result['id_channelengine_order'];
                $channelengine_shipment_id = $result['id_channelengine_shipment'];

                if ($channelengine_order_id != 0) {
                    $carrier = new Carrier($order->id_carrier);
                    $products = $order->getProducts();
                    $shipmentLines = array();

                    foreach ($products as $key => $product) {
                        $product_id = $product['product_id'] . ($product['product_attribute_id'] ? ('-'. $product['product_attribute_id']) : '');
                        $shippedQty = (int) $product['product_quantity'];
                        if ($shippedQty == 0) {
                            continue;
                        }
                        $shipmentLine = new \ChannelEngine\Merchant\ApiClient\Model\MerchantShipmentLineRequest();
                        $shipmentLine->setMerchantProductNo($product_id);
                        $shipmentLine->setQuantity($shippedQty);
                        $shipmentLines[] = $shipmentLine;
                    }

                    $shipmentData = array(
                        'merchantShipmentNo' => $order->getIdOrderCarrier(),
                        'merchantOrderNo' => $order->id,
                        'method' => $carrier->name,
                        'lines' => $shipmentLines,
                    );

                    $shipment = new \ChannelEngine\Merchant\ApiClient\Model\MerchantShipmentRequest($shipmentData);
                    if ($order->shipping_number) {
                        $shipment->setTrackTraceNo($order->shipping_number);
                    }
                    
                    if ($channelengine_shipment_id == 0) { //always true !
                        $result = $shipmentApi->shipmentCreateWithHttpInfo($shipment);
                        if ($result[0] instanceof ChannelEngine\Merchant\ApiClient\Model\ApiResponse &&  $result[0]->getSuccess()) {
                            Db::getInstance()->update('orders', array('id_channelengine_shipment' => 1), 'id_order = ' . $id_order);
                        }
                    }
                }
            } catch (Exception $e) {
                $doLog = true;
                if ($e->getCode() == 409) {
                    //shipment already exists. Mark as processed.
                    Db::getInstance()->update('orders', array('id_channelengine_shipment' => 1), 'id_order = ' . $id_order);
                    $doLog = true;
                }
                if ($doLog) {
                    $serverLog = print_r($e->getResponseObject(),true);
                    $this->logMessage('cronShipmentSync shipmentCreateWithHttpInfo: '. $serverLog . ' | ' . $e->getMessage(), 3 , $e->getCode());
                    // Print the exception
                    $this->pr($e->getMessage());
                }
            }
        }
    }

    private function getCreditOrders() {
        $order_slip_ids = array();

        $query = 'SELECT os.id_order_slip FROM ' . _DB_PREFIX_ . 'order_slip os
           LEFT JOIN ' . _DB_PREFIX_. 'orders o ON o.id_order = os.id_order
           WHERE o.id_channelengine_order > 0 AND os.id_channelengine_return = 0';

        $result = Db::getInstance('_PS_USE_SQL_SLAVE_')->executeS($query);
        foreach( $result as $row) {
            $order_slip_ids[] = $row['id_order_slip'];
        }
        return $order_slip_ids;
    }

    /**
     * @param $order_slip_ids
     * Send order credit info (returned products) to ChannelEngine
     */
    private function putPrestaCreditsToChannelEngine($order_slip_ids)
    {

        $this->getApiConfig();
        $returnApi = new \ChannelEngine\Merchant\ApiClient\Api\ReturnApi(null, $this->apiConfig);

        foreach($order_slip_ids as $order_slip_id) {
            try {

                $order_slip = new OrderSlip($order_slip_id);

                $orderSlipLines = array();
                //get order detail lines
                $order_slip_details = $order_slip->getProducts();

                foreach ($order_slip_details as $order_slip_detail) {
                    $orderSlipLine = new \ChannelEngine\Merchant\ApiClient\Model\MerchantReturnLineRequest();
                    $id = $order_slip_detail['product_id'] . ($order_slip_detail['product_attribute_id'] ? '-'.$order_slip_detail['product_attribute_id'] : ''); //get from id_order_detail
                    $orderSlipLine->setMerchantProductNo($id);
                    $orderSlipLine->setQuantity($order_slip_detail['product_quantity']);
                    $orderSlipLines[] = $orderSlipLine;
                }

                $returnRequest = new \ChannelEngine\Merchant\ApiClient\Model\MerchantReturnRequest();
                $returnRequest->setMerchantReturnNo($order_slip->id);
                $returnRequest->setMerchantOrderNo($order_slip->id_order);
                $returnRequest->setRefundExclVat($order_slip->total_products_tax_excl + $order_slip->total_shipping_tax_excl);
                $returnRequest->setRefundInclVat($order_slip->total_products_tax_incl + $order_slip->total_shipping_tax_incl);
                $returnRequest->setReason($returnRequest::REASON_PRODUCT_UNSATISFACTORY); //ok ?
                $returnRequest->setLines($orderSlipLines);

                $result = $returnApi->returnDeclareForMerchantWithHttpInfo($returnRequest);

                //set id_channelengine_return.
                if ($result[0] instanceof ChannelEngine\Merchant\ApiClient\Model\ApiResponse &&  $result[0]->getSuccess()) {
                    $id_channelengine_return = 1;
                    Db::getInstance()->update('order_slip', array('id_channelengine_return' => $id_channelengine_return), 'id_order_slip = ' . $order_slip_id);
                }


            } catch (Exception $e) {
                $doLog = true;
                if ($e->getCode() == 409) {
                    //return already exists. Mark as processed ?
                    $id_channelengine_return = 1;
                    Db::getInstance()->update('order_slip', array('id_channelengine_return' => $id_channelengine_return), 'id_order_slip = ' . $order_slip_id);
                    $doLog = false;
                }
                if ($doLog) {
                    $serverLog = print_r($e->getResponseObject(),true);
                    $this->logMessage('cronCreditSync: ' . $serverLog . ' | ' .  $e->getMessage(), 3 , $e->getCode());
                    // Print the exception
                    $this->pr($e->getMessage());
                }
                if ($e->getCode() == 0) {
                    //api connection problem. Do not process remaining records.
                    $this->logMessage('cronCreditSync: connection error.', 3 , $e->getCode());
                    break;
                }
            }
        }


    }
    private function getCarrierId()
    {
        $id_carrier = Configuration::get('CHANNELENGINE_CARRIER');
        $carrier = new Carrier($id_carrier);
        //get latest version of this carrier. id_carrier changes when updating a carrier.
        if (Validate::isLoadedObject($carrier)) {
            if ($carrier->deleted) {
                $carrier = Carrier::getCarrierByReference($carrier->id_reference);
            }
        }

        if (Validate::isLoadedObject($carrier)) {
            return $carrier->id;
        }
        return false;
    }

    /**
     * @param string $msg
     * @param int    $severity (1-5)
     * @param int    $error_code ()
     */
    private function logMessage($msg = '', $severity = 3, $error_code = 0)
    {
        //log to prestashop log
        $logToPS = true; //todo future: add to configuration of module
        if ($logToPS) {
            PrestaShopLogger::addLog($msg, $severity, $error_code, $this->name, 1);
        }

        //log to file
        $logToFile = false; //todo future: add to config of module
        if ($logToFile) {
            $logger = new FileLogger();
            $logger->setFilename(_PS_ROOT_DIR_.'/log/'.date('Ymd').'_channelengine.log'); //log to module folder ?
            $logger->logError($msg);
        }
    }

    private function pr($data) {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }

    /**
     * @param $item Channelengine orderline
     */
    protected function createOrderDetail(\ChannelEngine\Merchant\ApiClient\Model\MerchantOrderLineResponse $item, $orderId, $addressId, $funcOriginal = 'Original')
    {
        $productId = $item->getMerchantProductNo();
        $productAttributeId = 0;
        if (strpos($item->getMerchantProductNo(), '-') !== false) {
            $getMerchantProductNo = explode("-", $item->getMerchantProductNo());
            $productId = $getMerchantProductNo[0];
            $productAttributeId = $getMerchantProductNo[1];
        }

        $context = Context::getContext();
        $id_lang = (int)Configuration::get('CHANNELENGINE_SYNC_LANG');

        $product = new Product($productId, false, $id_lang);
        $productReference = $product->reference;
        $productName = $product->name;

        if ($productAttributeId) {
            $attributes = Product::getAttributesParams($productId, $productAttributeId);
            $nameprefix = ' - ';
            foreach ($attributes as $attribute) {
                $productName .= $nameprefix . $attribute['group'].' : '.$attribute['name'];
                $nameprefix = ', ';
            }
            $combination = new Combination($productAttributeId);

            if(!empty($combination->reference)) {
                $productReference = $combination->reference;
            }
        }

        //get advanced stock management warehouse info
        $id_warehouse = 0;
        $stock_management_active = Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT');
        if ($stock_management_active && (int)$product->advanced_stock_management == 1) {
            $warehouses = Warehouse::getProductWarehouseList($productId, $productAttributeId, $context->shop->id);
            foreach ($warehouses as $warehouse) {
                $id_warehouse = $warehouse['id_warehouse'];
                break;
            }
        }

        $orderDetail = new OrderDetail();
        $orderDetail->id_order = $orderId;
        $orderDetail->product_id = $productId;
        $orderDetail->product_attribute_id = $productAttributeId;
        $orderDetail->id_warehouse = $id_warehouse;
        $orderDetail->id_shop = $context->shop->id;
        $orderDetail->product_name = $productName;
        $orderDetail->product_quantity = $item->getQuantity();

        //Do not get tax from product. Can be different from calculated tax
        //Calculate tax. Round to nearest half 20.9->21 20.3->20.5
        $unitVat = (float)$item->{'get'.$funcOriginal.'UnitVat'}();
        $unitExclVat = (float)$item->{'get'.$funcOriginal.'UnitPriceInclVat'}() - (float)$item->{'get'.$funcOriginal.'UnitVat'}();
        $tax_rate = round(( $unitVat * 100 / $unitExclVat) * 2) / 2;

        $orderDetail->tax_rate = $tax_rate;
        $orderDetail->product_reference = $productReference;
        $orderDetail->unit_price_tax_incl = (float)$item->{'get'.$funcOriginal.'UnitPriceInclVat'}();
        //recalc price excl tax to get enough digit because data in $item is only 2 digits
        $orderDetail->unit_price_tax_excl = Tools::ps_round($orderDetail->unit_price_tax_incl / (1 + ($tax_rate/100)), _PS_PRICE_COMPUTE_PRECISION_);

        $orderDetail->product_price = $orderDetail->unit_price_tax_excl;
        $orderDetail->total_price_tax_incl = $orderDetail->unit_price_tax_incl * $orderDetail->product_quantity;
        $orderDetail->total_price_tax_excl = Tools::ps_round($orderDetail->total_price_tax_incl / (1 + ($tax_rate/100)), _PS_PRICE_COMPUTE_PRECISION_);

        $orderDetail->original_product_price = $product->price;
        //$orderDetail->original_wholesale_price = $product->wholesale_price;
        //$orderDetail->product_quantity_in_stock = ?? //future set correct stock info
        //$orderDetail->product_ean13

        return $orderDetail->add();
    }

    /**
     * @param $item Channelengine orderline
     */
    protected function createCartDetail(\ChannelEngine\Merchant\ApiClient\Model\MerchantOrderLineResponse $item, $cartId, $addressId, $funcOriginal = 'Original')
    {
        $productId = $item->getmerchantProductNo();
        $productAttributeId = 0;
        if (strpos($item->getmerchantProductNo(), '-') !== false) {
            $getMerchantProductNo = explode("-", $item->getMerchantProductNo());
            $productId = $getMerchantProductNo[0];
            $productAttributeId = $getMerchantProductNo[1];
        }

        $context = Context::getContext();
        $id_lang = (int)Configuration::get('CHANNELENGINE_SYNC_LANG');

        $product = new Product($productId, false, $id_lang);

        $productName = $product->name;
        if ($productAttributeId) {
            $attributes = Product::getAttributesParams($productId, $productAttributeId);
            $nameprefix = ' - ';
            foreach ($attributes as $attribute) {
                $productName .= $nameprefix . $attribute['group'].' : '.$attribute['name'];
                $nameprefix = ', ';
            }
        }

        $result_add = Db::getInstance()->insert('cart_product', array(
            'id_product' =>            (int)$productId,
            'id_product_attribute' =>    (int)$productAttributeId,
            'id_cart' =>                (int)$cartId,
            'id_address_delivery' =>    (int)$addressId,
            'id_shop' =>                $context->shop->id,
            'quantity' =>                $item->getQuantity(),
            'date_add' =>                date('Y-m-d H:i:s')
        ));

        return $result_add;

    }
}