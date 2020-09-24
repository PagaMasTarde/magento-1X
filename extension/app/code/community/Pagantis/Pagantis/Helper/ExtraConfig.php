<?php

/**
 * Class Pagantis_Pagantis_Helper_Data
 */
class Pagantis_Pagantis_Helper_ExtraConfig extends Mage_Core_Helper_Abstract
{
    /**
     * Config tablename
     */
    const CONFIG_TABLE = 'pagantis_config';

    /**
     * @var string $tablename
     */
    protected $tableName = null;

    /**
     * @var Magento_Db_Adapter_Pdo_Mysql $dbObject
     */
    protected $dbObject;


    /**
     * ExtraConfig constructor.
     */
    public function __construct()
    {
        $this->tableName = Mage::getSingleton('core/resource')->getTableName(self::CONFIG_TABLE);
        $this->dbObject = Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    /**
     * @return array
     */
    public function getExtraConfig()
    {
        $data = array();
        $result = $this->dbObject->fetchAll("select * from " . $this->tableName);
        if (count($result)) {
            foreach ($result as $value) {
                $data[$value['config']] = $value['value'];
            }
        }
        return $data;
    }
}
