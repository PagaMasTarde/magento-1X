<?php

/**
 * Class Pagantis_Pagantis_Model_Resource_Order
 */
class Pagantis_Pagantis_Model_Resource_Concurrency extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('pagantis/concurrency', 'id');
    }
}
