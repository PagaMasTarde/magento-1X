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
        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $checkoutSession = Mage::getModel('checkout/session');
        $config = Mage::getStoreConfig('payment/paylater');

        $this->assign(array(
            'enabled' => $config['active'],
            'publicKey' => $config['PAYLATER_PROD'] ? $config['PAYLATER_PUBLIC_KEY_PROD'] : $config['PAYLATER_PUBLIC_KEY_TEST'],
            'simulatorType' => $config['PAYLATER_CHECKOUT_HOOK_TYPE'],
            'amount' => $checkoutSession->getQuote()->getGrandTotal(),
            'defaultInstallments' => $config['DEFAULT_INSTALLMENTS'],
            'maxInstallments' => $config['MAX_INSTALLMENTS'],
            'minAmount' => $config['MIN_AMOUNT']
        ));

        $title = $config['PAYLATER_TITLE'];
        if (empty($title)) {
            $title = $config['title'];
        }

        $classCoreTemplate = Mage::getConfig()->getBlockClassName('core/template');
        $logoTemplate = new $classCoreTemplate;
        $logoHtml = $logoTemplate->setTemplate('pmt/checkout/logo.phtml')->toHtml();

        $template = $this->setTemplate('pmt/checkout/paylater.phtml');
        $template->setMethodTitle($title)->setMethodLabelAfterHtml($logoHtml);

        parent::_construct();
    }
}
