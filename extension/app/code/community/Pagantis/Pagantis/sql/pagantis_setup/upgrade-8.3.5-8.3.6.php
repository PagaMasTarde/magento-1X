<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("INSERT INTO `pagantis_config` 
    (`config`, `value`)
    VALUES
    ('PAGANTIS_SIMULATOR_DISPLAY_TYPE_CHECKOUT', 'pgSDK.simulator.types.CHECKOUT_PAGE')");

$installer->run("UPDATE `pagantis_config` 
    SET `value` = 'pgSDK.simulator.types.PRODUCT_PAGE'
    WHERE `config` = 'PAGANTIS_SIMULATOR_DISPLAY_TYPE'");

$installer->endSetup();
