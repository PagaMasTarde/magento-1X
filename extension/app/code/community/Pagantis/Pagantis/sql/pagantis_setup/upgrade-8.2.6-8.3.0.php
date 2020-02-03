<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run(
    "UPDATE `pagantis_config` SET `value` = `pgSDK.simulator.types.SELECTABLE_TEXT_CUSTOM` 
        WHERE `config` = `PAGANTIS_SIMULATOR_DISPLAY_TYPE`"
);
$installer->run(
    "UPDATE `pagantis_config` SET `value` = `hookDisplayProductButtons` 
        WHERE `config` = `PAGANTIS_SIMULATOR_DISPLAY_POSITION`"
);

$installer->endSetup();
