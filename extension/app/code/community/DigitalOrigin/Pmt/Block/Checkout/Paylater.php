<?php

/**
 * Class DigitalOrigin_Pmt_Block_Form_Paylater
 */
class DigitalOrigin_Pmt_Block_Checkout_Paylater extends Mage_Payment_Block_Form
{
    /**
     * Form constructor
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

        $this->assign(
            [
                'amount' => $amount,
                'publicKey' => $publicKey,
                'simulatorType' => $simulatorType
            ]
        );

        $this->setTemplate('pmt/checkout/paylater.phtml')->setMethodTitle(
            Mage::helper('pmt')->__($config['title'] .
            ' '.
            $config['TITLE_EXTRA'])
        );

        return parent::_construct();
    }
}
