<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// Re-Create pagantis_order table to add token field
$installer->run('DROP TABLE IF EXISTS `pagantis_order`');
$name = $this->modelTable['pagantis/order'];
$sql = 'CREATE TABLE `pagantis_order` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `mg_order_id` varchar(50) NOT NULL,
  `token` varchar(32) NOT NULL,
  `pagantis_order_id` varchar(50), 
  PRIMARY KEY (`id`),
  UNIQUE KEY (`mg_order_id`, `token`)
  )';
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$writeConnection->query($sql);

$installer->endSetup();
