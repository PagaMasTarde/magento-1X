<?php

/**
 * Class Pagantis_Pagantis_Helper_Data
 */
class Pagantis_Pagantis_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfig('payment/pagantis/active');
    }
}
