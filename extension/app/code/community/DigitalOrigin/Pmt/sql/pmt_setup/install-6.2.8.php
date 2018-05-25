<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run('DROP TABLE IF EXISTS pmt_transactions');
$installer->run('CREATE TABLE `pmt_cart_process` (
  `id` INT NOT NULL ,
  `timestamp` INT NOT NULL ,
  PRIMARY KEY (`id`)
  )');

$installer->endSetup();