<?php

/**
 * Class Pagantis_Pagantis_Model_Concurrency
 */
class Pagantis_Pagantis_Model_Concurrency extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('pagantis/concurrency');
    }
}
