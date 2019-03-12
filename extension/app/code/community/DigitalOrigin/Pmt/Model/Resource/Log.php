<?php

/**
 * Class DigitalOrigin_Pmt_Model_Resource_Log
 */
class DigitalOrigin_Pmt_Model_Resource_Log extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('pmt/log', 'id');
    }
}
