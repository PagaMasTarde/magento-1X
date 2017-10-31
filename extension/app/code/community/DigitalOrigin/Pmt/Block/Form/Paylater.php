<?php

class DigitalOrigin_Pmt_Block_Form_Paylater extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        $quote = Mage::getModel('checkout/session')->getQuote();
        $config = Mage::getStoreConfig('payment/paylater');

        $quoteData= $quote->getData();
        $amount=$quoteData['grand_total'];

        $this->setData('total', $amount);

        $this->setTemplate('pmt/form/paylater.phtml')->setMethodTitle(Mage::helper('pmt')->__($config['title']));

        return parent::_construct();
    }
}
