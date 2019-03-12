<?php

/**
 * Class DigitalOrigin_Pmt_Model_Order
 */
class DigitalOrigin_Pmt_Model_Order extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('pmt/order');
    }
}
