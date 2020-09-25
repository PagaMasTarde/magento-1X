<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run('DROP TABLE IF EXISTS pagantis_cart_concurrency');
$installer->run('CREATE TABLE `pagantis_cart_concurrency` (
  `id` varchar(50) NOT NULL,
  `timestamp` INT NOT NULL,
  PRIMARY KEY (`id`)
  )');

$installer->endSetup();
