<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$this->tableName = Mage::getSingleton('core/resource')->getTableName('pagantis_config');
$installer->run("INSERT INTO `' . $this->tableName . '` 
    (`config`, `value`)
    VALUES
    ('PAGANTIS_DISPLAY_MAX_AMOUNT', '1500')");

$installer->endSetup();
