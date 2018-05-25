<?php

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('pmt/transaction'))
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ),
        'Unique identifier'
    )
    ->addColumn(
        'timestamp',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'identity' => false,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => false,
        ),
        'Timestamp'
    );

if (!$installer->getConnection()->isTableExists($table->getName())) {
    $installer->getConnection()->createTable($table);
}

$installer->endSetup();