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

function upgrade_module_2_2_8($module)
{

    $result = true;
    $sql = array();

    $sql[] = "ALTER TABLE `" . _DB_PREFIX_ . "orders` "
        . "ADD `channelengine_channel_tenant` VARCHAR(24) NULL DEFAULT '' AFTER `channelengine_channel_name`";

    foreach($sql as $script) {
        $result &= Db::getInstance()->execute($script);
    }


    //upgrade the module config.
    if (!Configuration::hasKey('CHANNELENGINE_ACCOUNTS', null) ||  !Configuration::get('CHANNELENGINE_ACCOUNTS', null)) {
        $ceAccountName = Configuration::get('CHANNELENGINE_ACCOUNT_NAME', null);
        $ceApiKey = Configuration::get('CHANNELENGINE_ACCOUNT_API_KEY', null);
        if ($ceAccountName  && $ceApiKey) {
            Configuration::updateValue('CHANNELENGINE_ACCOUNTS', $ceAccountName . '|' . $ceApiKey);
        }
    }

    return $result;

}
