<?php

/**
 * Class DigitalOrigin_Pmt_Model_Resource_Order
 */
class DigitalOrigin_Pmt_Model_Resource_Concurrency extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('pmt/concurrency', 'id');
    }
}
