<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run('DROP TABLE IF EXISTS `pmt_orders`');
$installer->run('CREATE TABLE `pmt_orders` (
  `mg_order_id` varchar(50) NOT NULL, 
  `pmt_order_id` varchar(50), 
  PRIMARY KEY (`pmt_order_id`)
  )');

$installer->run('DROP TABLE IF EXISTS `pmt_logs`');
$installer->run('CREATE TABLE `pmt_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `log` TEXT,
  `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
  )');

$installer->endSetup();