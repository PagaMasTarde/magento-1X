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

// Create pagantis_order table
$this->tableName = Mage::getSingleton('core/resource')->getTableName('pagantis_order');
$installer->run('DROP TABLE IF EXISTS ' . $this->tableName);
$sql = 'CREATE TABLE `' . $this->tableName . '` (
  `mg_order_id` varchar(50) NOT NULL,
  `token` varchar(32) NOT NULL,
  `pagantis_order_id` varchar(50), 
  PRIMARY KEY (`mg_order_id`, `token`)
  )';
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$writeConnection->query($sql);

// Create pagantis_config table
$this->tableName = Mage::getSingleton('core/resource')->getTableName('pagantis_config');
$installer->run('DROP TABLE IF EXISTS ' . $this->tableName);
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
$installer->run("INSERT INTO `$this->tableName` 
    (`config`, `value`)
    VALUES
    ('URL_OK', 'checkout/onepage/success/'),
    ('URL_KO', 'checkout/cart/'),
    ('ALLOWED_COUNTRIES', '[\"ES\",\"FR\",\"IT\",\"GB\"]'),
    ('SIMULATOR_IS_ENABLED', true),
    ('PRICE_SELECTOR', '[id^=\'product-price\']'),
    ('PRICE_SELECTOR_CONTAINER', '.price-info')");

$installer->endSetup();

