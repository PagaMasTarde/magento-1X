<?php

/**
 * Class DigitalOrigin_Pmt_Model_Resource_Order_Collection
 */
class DigitalOrigin_Pmt_Model_Resource_Order_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('pmt/order');
    }
}