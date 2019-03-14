<?php

/**
 * Class Pagantis_Pagantis_Model_Log
 */
class Pagantis_Pagantis_Model_Log extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('pagantis/log');
    }
}
