<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// create pagantis_cart_concurrency table
$this->tableName = Mage::getSingleton('core/resource')->getTableName('pagantis_cart_concurrency');
$installer->run('DROP TABLE IF EXISTS ' . $this->tableName);
$sql = 'CREATE TABLE `' . $this->tableName . '` (
  `id` VARCHAR(50) NOT NULL,
  `timestamp` INT NOT NULL,
  PRIMARY KEY (`id`)
  )';
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$writeConnection->query($sql);

$installer->endSetup();
