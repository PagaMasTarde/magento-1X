<?php

/**
 * Class Clearpay_Clearpay_Model_Resource_Config_Collection
 */
class Clearpay_Clearpay_Model_Resource_Config_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('clearpay/config');
    }
}
