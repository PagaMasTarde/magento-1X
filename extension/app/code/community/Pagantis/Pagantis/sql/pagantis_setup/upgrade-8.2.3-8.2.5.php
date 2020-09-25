<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$this->tableName = Mage::getSingleton('core/resource')->getTableName('pagantis_config');
$installer->run("UPDATE `$this->tableName` 
    SET `value` = 'a:3:{i:0;s:2:\"es\";i:1;s:2:\"it\";i:2;s:2:\"fr\";}'
    WHERE `config` = 'PAGANTIS_ALLOWED_COUNTRIES'");

$installer->endSetup();
