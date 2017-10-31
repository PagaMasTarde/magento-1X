<?php

class DigitalOrigin_Pmt_Model_Payment extends Mage_Payment_Model_Method_Abstract {

    protected $_code  = 'pmt';
    protected $_formBlockType = 'pmt/form_paylater';
    protected $_infoBlockType = 'pmt/info_paylater';

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
        return Mage::getUrl('pmt/paylater/redirect', array('_secure' => false));
    }
}
