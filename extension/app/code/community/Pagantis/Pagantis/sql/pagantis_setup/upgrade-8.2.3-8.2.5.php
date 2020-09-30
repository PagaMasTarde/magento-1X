<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$this->tableName = Mage::getSingleton('core/resource')->getTableName('pagantis_config');
$installer->run("INSERT INTO `$this->tableName` 
    (`config`, `value`)
    VALUES
    ('PAGANTIS_ALLOWED_COUNTRIES', 'a:3:{i:0;s:2:\"es\";i:1;s:2:\"it\";i:2;s:2:\"fr\";}')");

$installer->endSetup();
