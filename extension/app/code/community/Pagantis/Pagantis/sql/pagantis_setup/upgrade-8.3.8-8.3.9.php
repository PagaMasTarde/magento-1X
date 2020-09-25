<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// Re-Create pagantis_order table to add token field
$this->tableName = Mage::getSingleton('core/resource')->getTableName('pagantis_order');
$sql = 'alter table `' . $this->tableName . '` add column token varchar(32) not null AFTER `mg_order_id`;';
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$writeConnection->query($sql);

$sql = 'ALTER TABLE `' . $this->tableName . '` DROP PRIMARY KEY, ADD PRIMARY KEY(`mg_order_id`, `token`);';
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$writeConnection->query($sql);

$installer->endSetup();
