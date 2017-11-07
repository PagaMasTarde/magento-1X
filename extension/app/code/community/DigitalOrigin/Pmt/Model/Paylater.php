<?php

/**
 * Class DigitalOrigin_Pmt_Model_Paylater
 */
class DigitalOrigin_Pmt_Model_Paylater extends Mage_Payment_Model_Method_Abstract
{
    /**
     * @var string
     */
    protected $_code  = 'paylater';

    /**
     * @var string
     */
    protected $_formBlockType = 'pmt/checkout_paylater';

    /**
     * @param mixed $data
     *
     * @return $this
     */
    public function assignData($data)
    {
        $this->getInfoInstance();

        return $this;
    }

    /**
     * @return $this
     */
    public function validate()
    {
        parent::validate();

        $this->getInfoInstance();

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('pmt/payment', array('_secure' => false));
    }

    /**
     * @param null $quote
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        $this->_minOrderTotal = $this->getConfigData('MIN_AMOUNT');
        if ($quote && $quote->getBaseGrandTotal() < $this->_minOrderTotal) {
            return false;
        }

        return parent::isAvailable();
    }
}
