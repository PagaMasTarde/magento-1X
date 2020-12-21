<?php
die("llega al install");
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

// create clearpay_cart_concurrency table
$this->tableName = Mage::getSingleton('core/resource')->getTableName('clearpay_cart_concurrency');
$installer->run('DROP TABLE IF EXISTS ' . $this->tableName);
$sql = 'CREATE TABLE `' . $this->tableName . '` (
  `id` VARCHAR(50) NOT NULL,
  `timestamp` INT NOT NULL,
  PRIMARY KEY (`id`)
  )';
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$writeConnection->query($sql);

// Create clearpay_order table
$this->tableName = Mage::getSingleton('core/resource')->getTableName('clearpay_order');
$installer->run('DROP TABLE IF EXISTS ' . $this->tableName);
$sql = 'CREATE TABLE `' . $this->tableName . '` (
  `mg_order_id` varchar(50) NOT NULL,
  `token` varchar(32) NOT NULL,
  `clearpay_order_id` varchar(50), 
  PRIMARY KEY (`mg_order_id`, `token`),
  )';
$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');
$writeConnection->query($sql);

// Create clearpay_log table
$this->tableName = Mage::getSingleton('core/resource')->getTableName('clearpay_log');
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

// Create clearpay_config table
$this->tableName = Mage::getSingleton('core/resource')->getTableName('clearpay_config');
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
    ('CLEARPAY_URL_OK', 'checkout/onepage/success/'),
    ('CLEARPAY_URL_KO', 'checkout/cart/'),
    ('CLEARPAY_ALLOWED_COUNTRIES', '[\"es\",\"fr\",\"it\"]'),
    ('CLEARPAY_SIMULATOR_CSS_SELECTOR', 'default')");

$installer->endSetup();
