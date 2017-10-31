<?php

class DigitalOrigin_Pmt_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/pmt/active');
    }
}
