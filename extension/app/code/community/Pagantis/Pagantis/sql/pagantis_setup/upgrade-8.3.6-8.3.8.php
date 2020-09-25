<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$this->tableName = Mage::getSingleton('core/resource')->getTableName('pagantis_config');
$installer->run("INSERT INTO `' . $this->tableName . '` 
    (`config`, `value`)
    VALUES
    ('PAGANTIS_SIMULATOR_CSS_POSITION_SELECTOR_CHECKOUT', 'default')");

$installer->run("INSERT INTO `' . $this->tableName . '` 
    (`config`, `value`)
    VALUES
    ('PAGANTIS_SIMULATOR_CSS_PRICE_SELECTOR_CHECKOUT', 'default')");

$installer->endSetup();
