<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// Create pagantis_order table
$this->tableName = Mage::getSingleton('core/resource')->getTableName('pagantis_order');
$installer->run('DROP TABLE IF EXISTS ' . $this->tableName);
$sql = 'CREATE TABLE `' . $this->tableName . '` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `mg_order_id` varchar(50) NOT NULL,
  `pagantis_order_id` varchar(50), 
  PRIMARY KEY (`id`),
  UNIQUE KEY (`mg_order_id`, `token`)
  )';
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$writeConnection->query($sql);

// Create pagantis_log table
$this->tableName = Mage::getSingleton('core/resource')->getTableName('pagantis_log');
$installer->run('DROP TABLE IF EXISTS ' . $this->tableName);
$sql = 'CREATE TABLE `' . $this->tableName . '` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `log` TEXT,
  `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
  )';
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$writeConnection->query($sql);

$installer->endSetup();
