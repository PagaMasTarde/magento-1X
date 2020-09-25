<?php

/**
 * Attribute:
 */

$code = 'pagantis_promoted';
$group = 'General';
$label = 'Pagantis Promoted';

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
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

$this->tableName = Mage::getSingleton('core/resource')->getTableName('pagantis_config');
$installer->run("INSERT INTO `' . $this->tableName . '` 
    (`config`, `value`)
    VALUES
    ('PAGANTIS_PROMOTION_MESSAGE', 'Finance this product <span class=\"pmt-no-interest\">without interest!</span>')");

$installer->endSetup();