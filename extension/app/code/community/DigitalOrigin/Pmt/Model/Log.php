<?php

/**
 * Class DigitalOrigin_Pmt_Model_Log
 */
class DigitalOrigin_Pmt_Model_Log extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('pmt/log');
    }
}
