<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run('DROP TABLE IF EXISTS `pagantis_config`');
$installer->run('CREATE TABLE `pagantis_config` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `config` VARCHAR(60) NOT NULL,
  `value` VARCHAR(1000) NOT NULL,
  PRIMARY KEY (`id`)
  )');

$installer->run("INSERT INTO `pagantis_config` 
    (`config`, `value`)
    VALUES
    ('PAGANTIS_TITLE', 'Instant Financing'),
    ('PAGANTIS_SIMULATOR_DISPLAY_TYPE', 'pmtSDK.simulator.types.SIMPLE'),
    ('PAGANTIS_SIMULATOR_DISPLAY_SKIN', 'pmtSDK.simulator.skins.BLUE'),
    ('PAGANTIS_SIMULATOR_DISPLAY_POSITION', 'hookDisplayProductButtons'),
    ('PAGANTIS_SIMULATOR_START_INSTALLMENTS', '3'),
    ('PAGANTIS_SIMULATOR_CSS_POSITION_SELECTOR', 'default'),
    ('PAGANTIS_SIMULATOR_DISPLAY_CSS_POSITION', 'pmtSDK.simulator.positions.INNER'),
    ('PAGANTIS_SIMULATOR_CSS_PRICE_SELECTOR', 'default'),
    ('PAGANTIS_SIMULATOR_CSS_QUANTITY_SELECTOR', 'default'),
    ('PAGANTIS_FORM_DISPLAY_TYPE', '0'),
    ('PAGANTIS_DISPLAY_MIN_AMOUNT', '1'),
    ('PAGANTIS_URL_OK', 'checkout/onepage/success/'),
    ('PAGANTIS_URL_KO', 'checkout/cart/')");

$installer->endSetup();
