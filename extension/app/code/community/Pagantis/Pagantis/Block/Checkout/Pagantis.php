<?php

/**
 * Class Pagantis_Pagantis_Block_Form_Pagantis
 */
class Pagantis_Pagantis_Block_Checkout_Pagantis extends Mage_Payment_Block_Form
{
    /**
     * Form constructor
     */
    protected function _construct()
    {
        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $config = Mage::getStoreConfig('payment/pagantis');
        $extraConfig = Mage::helper('pagantis/ExtraConfig')->getExtraConfig();
        $locale = substr(Mage::app()->getLocale()->getLocaleCode(), -2, 2);
        $checkoutSession = Mage::getModel('checkout/session');
        $quote = $checkoutSession->getQuote();
        $amount = $quote->getGrandTotal();

        $title = $this->__($extraConfig['PAGANTIS_TITLE']);
        $classCoreTemplate = Mage::getConfig()->getBlockClassName('core/template');

        $logoHtml = '';
        if ($config['active']) {
            $logoTemplate = new $classCoreTemplate;
            $logoTemplate->assign(array(
                'publicKey'          => $config['pagantis_public_key'],
                'amount'             => $amount,
                'locale'             => $locale,
                'pagantisIsEnabled'  => $config['active'],
                'simulatorIsEnabled' => $config['pagantis_simulator_is_enabled'],
            ));
            $logoHtml = $logoTemplate->setTemplate('pagantis/checkout/logo.phtml')->toHtml();

            if ($logoHtml == '') {
                $logoTemplate->_allowSymlinks = true;
                $logoHtml = $logoTemplate->setTemplate('pagantis/checkout/logo.phtml')->toHtml();
            }
        }

        $template = $this->setTemplate('pagantis/checkout/pagantis.phtml');

        if ($template->toHtml() == '') {
            $this->_allowSymlinks = true;
        }
        $template->setMethodTitle($title)->setMethodLabelAfterHtml($logoHtml);

        parent::_construct();
    }
}
