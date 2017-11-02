<?php

/**
 * Class DigitalOrigin_Pmt_Helper_Data
 */
class DigitalOrigin_Pmt_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/paylater/active');
    }
}
