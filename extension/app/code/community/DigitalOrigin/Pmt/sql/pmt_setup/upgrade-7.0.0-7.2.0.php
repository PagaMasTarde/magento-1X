<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run('DROP TABLE IF EXISTS `pmt_config`');
$installer->run('CREATE TABLE `pmt_config` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `config` VARCHAR(60) NOT NULL,
  `value` VARCHAR(1000) NOT NULL,
  PRIMARY KEY (`id`)
  )');

$installer->run("INSERT INTO `pmt_config` 
    (`config`, `value`)
    VALUES
    ('PMT_TITLE', 'Instant Financing'),
    ('PMT_SIMULATOR_DISPLAY_TYPE', 'pmtSDK.simulator.types.SIMPLE'),
    ('PMT_SIMULATOR_DISPLAY_SKIN', 'pmtSDK.simulator.skins.BLUE'),
    ('PMT_SIMULATOR_DISPLAY_POSITION', 'hookDisplayProductButtons'),
    ('PMT_SIMULATOR_START_INSTALLMENTS', '3'),
    ('PMT_SIMULATOR_CSS_POSITION_SELECTOR', 'default'),
    ('PMT_SIMULATOR_DISPLAY_CSS_POSITION', 'pmtSDK.simulator.positions.INNER'),
    ('PMT_SIMULATOR_CSS_PRICE_SELECTOR', 'default'),
    ('PMT_SIMULATOR_CSS_QUANTITY_SELECTOR', 'default'),
    ('PMT_FORM_DISPLAY_TYPE', '0'),
    ('PMT_DISPLAY_MIN_AMOUNT', '1'),
    ('PMT_URL_OK', 'checkout/onepage/success/'),
    ('PMT_URL_KO', 'checkout/cart/')");

$installer->endSetup();
