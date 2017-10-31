<?php

/**
 * Class DigitalOrigin_Pmt_Block_Form_Paylater
 */
class DigitalOrigin_Pmt_Block_Form_Paylater extends Mage_Payment_Block_Form
{
    /*
     * Constructor for the form.
     *
     * Fetch data from configuration and send it to the view
     */
    protected function _construct()
    {
        $quote = Mage::getModel('checkout/session')->getQuote();
        $config = Mage::getStoreConfig('payment/paylater');

        $quoteData= $quote->getData();
        $amount=$quoteData['grand_total'];
        $isProduction = $config['PAYLATER_PROD'];
        $publicKey = $isProduction ? $config['PAYLATER_PUBLIC_KEY_PROD'] : $config['PAYLATER_PUBLIC_KEY_TEST'];
        $simulatorType = $config['PAYLATER_CHECKOUT_HOOK_TYPE'];

        $this->setData('amount', $amount);
        $this->setData('publicKey', $publicKey);
        $this->setData('simulatorType', $simulatorType);

        $this->setTemplate('pmt/form/paylater.phtml')->setMethodTitle(Mage::helper('pmt')->__($config['title']));

        return parent::_construct();
    }
}
