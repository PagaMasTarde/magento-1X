<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("INSERT INTO `pagantis_config` 
    (`config`, `value`)
    VALUES
    ('PAGANTIS_SIMULATOR_THOUSANDS_SEPARATOR', '.'),
    ('PAGANTIS_SIMULATOR_DECIMAL_SEPARATOR', ',')");

$installer->endSetup();
