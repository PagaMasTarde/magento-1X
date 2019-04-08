<?php

/**
 * Class Pagantis_Pagantis_Model_Resource_Log
 */
class Pagantis_Pagantis_Model_Resource_Log extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('pagantis/log', 'id');
    }
}
