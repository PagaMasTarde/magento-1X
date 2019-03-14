<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run('DROP TABLE IF EXISTS `pagantis_orders`');
$installer->run('CREATE TABLE `pagantis_orders` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `mg_order_id` varchar(50) NOT NULL, 
  `pagantis_order_id` varchar(50), 
  PRIMARY KEY (`id`),
  UNIQUE KEY (`mg_order_id`)
  )');

$installer->run('DROP TABLE IF EXISTS `pagantis_logs`');
$installer->run('CREATE TABLE `pagantis_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `log` TEXT,
  `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
  )');

$installer->endSetup();
