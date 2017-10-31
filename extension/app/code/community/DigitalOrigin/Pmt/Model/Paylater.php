<?php

class DigitalOrigin_Pmt_Model_Paylater extends Mage_Payment_Model_Method_Abstract {

    protected $_code  = 'paylater';
    protected $_formBlockType = 'paylater/form_paylater';
    protected $_infoBlockType = 'paylater/info_paylater';

    public function assignData($data)
    {
        $this->getInfoInstance();

        return $this;
    }

    public function validate()
    {
        parent::validate();

        $this->getInfoInstance();

        return $this;
    }

    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('paylater/payment/redirect', array('_secure' => false));
    }
}
