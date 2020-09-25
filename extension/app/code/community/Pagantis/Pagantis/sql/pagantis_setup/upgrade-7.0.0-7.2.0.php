<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// Create pagantis_config table
$this->tableName = Mage::getSingleton('core/resource')->getTableName('pagantis_config');
$installer->run('DROP TABLE IF EXISTS ' . $this->tableName);
$name = $this->modelTable['pagantis/concurrency'];
$sql = 'CREATE TABLE `' . $this->tableName . '` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `config` VARCHAR(60) NOT NULL,
  `value` VARCHAR(1000) NOT NULL,
  PRIMARY KEY (`id`)
  )';
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$writeConnection->query($sql);

//Populate config table
$installer->run("INSERT INTO `' . $this->tableName . '` 
    (`config`, `value`)
    VALUES
    ('PAGANTIS_TITLE', 'Instant Financing'),
    ('PAGANTIS_SIMULATOR_DISPLAY_TYPE', 'pgSDK.simulator.types.PRODUCT_PAGE'),
    ('PAGANTIS_SIMULATOR_DISPLAY_SKIN', 'pgSDK.simulator.skins.BLUE'),
    ('PAGANTIS_SIMULATOR_DISPLAY_POSITION', 'hookDisplayProductButtons'),
    ('PAGANTIS_SIMULATOR_START_INSTALLMENTS', '3'),
    ('PAGANTIS_SIMULATOR_CSS_POSITION_SELECTOR', 'default'),
    ('PAGANTIS_SIMULATOR_DISPLAY_CSS_POSITION', 'pgSDK.simulator.positions.INNER'),
    ('PAGANTIS_SIMULATOR_CSS_PRICE_SELECTOR', 'default'),
    ('PAGANTIS_SIMULATOR_CSS_QUANTITY_SELECTOR', 'default'),
    ('PAGANTIS_FORM_DISPLAY_TYPE', '0'),
    ('PAGANTIS_DISPLAY_MIN_AMOUNT', '1'),
    ('PAGANTIS_URL_OK', 'checkout/onepage/success/'),
    ('PAGANTIS_URL_KO', 'checkout/cart/')");

$installer->endSetup();
