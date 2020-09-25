<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$this->tableName = Mage::getSingleton('core/resource')->getTableName('pagantis_config');
$installer->run("INSERT INTO `' . $this->tableName . '` 
    (`config`, `value`)
    VALUES
    ('PAGANTIS_SIMULATOR_DISPLAY_TYPE_CHECKOUT', 'pgSDK.simulator.types.CHECKOUT_PAGE')");

$installer->run("UPDATE `' . $this->tableName . '` 
    SET `value` = 'pgSDK.simulator.types.PRODUCT_PAGE'
    WHERE `config` = 'PAGANTIS_SIMULATOR_DISPLAY_TYPE'");

$installer->endSetup();
