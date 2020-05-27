<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("INSERT INTO `pagantis_config` 
    (`config`, `value`)
    VALUES
    ('PAGANTIS_SIMULATOR_CSS_POSITION_SELECTOR_CHECKOUT', 'default')");

$installer->run("INSERT INTO `pagantis_config` 
    (`config`, `value`)
    VALUES
    ('PAGANTIS_SIMULATOR_CSS_PRICE_SELECTOR_CHECKOUT', 'default')");

$installer->endSetup();
