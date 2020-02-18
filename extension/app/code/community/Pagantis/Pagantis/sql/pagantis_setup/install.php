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
    ('PAGANTIS_SIMULATOR_DISPLAY_TYPE', 'pgSDK.simulator.types.SIMPLE'),
    ('PAGANTIS_SIMULATOR_DISPLAY_SKIN', 'pgSDK.simulator.skins.BLUE'),
    ('PAGANTIS_SIMULATOR_DISPLAY_POSITION', 'hookDisplayProductButtons'),
    ('PAGANTIS_SIMULATOR_START_INSTALLMENTS', '3'),
    ('PAGANTIS_SIMULATOR_CSS_POSITION_SELECTOR', 'default'),
    ('PAGANTIS_SIMULATOR_DISPLAY_CSS_POSITION', 'pgSDK.simulator.positions.INNER'),
    ('PAGANTIS_SIMULATOR_CSS_PRICE_SELECTOR', 'default'),
    ('PAGANTIS_SIMULATOR_CSS_QUANTITY_SELECTOR', 'default'),
    ('PAGANTIS_FORM_DISPLAY_TYPE', '0'),
    ('PAGANTIS_DISPLAY_MIN_AMOUNT', '1'),
    ('PAGANTIS_DISPLAY_MAX_AMOUNT', '0'),
    ('PAGANTIS_URL_OK', 'checkout/onepage/success/'),
    ('PAGANTIS_ALLOWED_COUNTRIES', 'a:3:{i:0;s:2:\"es\";i:1;s:2:\"it\";i:2;s:2:\"fr\";}'),
    ('PAGANTIS_URL_KO', 'checkout/cart/'),
    ('PAGANTIS_SIMULATOR_THOUSANDS_SEPARATOR', '.'),
    ('PAGANTIS_SIMULATOR_DECIMAL_SEPARATOR', ',')");

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$setup->addAttribute('catalog_product', $code, array(
    'group'         => $group,
    'input'         => 'boolean',
    'type'          => 'int',
    'label'         => $label,
    'source'        => 'eav/entity_attribute_source_boolean',
    'visible'       => true,
    'visible_on_front' => false,
    'required'      => false,
    'user_defined'  => false,
    'searchable'    => true,
    'filterable'    => true,
    'comparable'    => true,
    'used_in_product_listing' => true,
    'visible_in_advanced_search'  => true,
    'is_html_allowed_on_front' => false,
    'unique'        => false,
    'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
));

$installer->endSetup();
