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
        $allowedCountries = unserialize($extraConfig['PAGANTIS_ALLOWED_COUNTRIES']);
        $promotedAmount =  0;
        $cart = Mage::getModel('checkout/cart')->getQuote();
        foreach ($cart->getAllVisibleItems() as $item) {
            $magentoProduct = $item->getProduct();
            $pagantisPromoted = $magentoProduct->getData("pagantis_promoted") ? 1 : 0;
            $productPrice = $item->getRowTotalInclTax();
            if ($pagantisPromoted) {
                $promotedAmount += $productPrice;
            }
        }

        if (in_array(strtolower($locale), $allowedCountries)) {
            $title = $this->__($extraConfig['PAGANTIS_TITLE']);
            $classCoreTemplate = Mage::getConfig()->getBlockClassName('core/template');

            $logoHtml = '';
            if ($config['active']) {
                $logoTemplate = new $classCoreTemplate;
                $logoTemplate->assign(array(
                    'locale'             => $locale,
                ));
                $logoHtml = $logoTemplate->setTemplate('pagantis/checkout/logo.phtml')->toHtml();

                if ($logoHtml == '') {
                    $logoTemplate->_allowSymlinks = true;
                    $logoHtml = $logoTemplate->setTemplate('pagantis/checkout/logo.phtml')->toHtml();
                }
            }

            $template = $this->setTemplate('pagantis/checkout/pagantis.phtml');
            $template->assign(array(
                'publicKey'          => $config['pagantis_public_key'],
                'amount'             => $amount,
                'promotedAmount'     => $promotedAmount,
                'locale'             => $locale,
                'pagantisIsEnabled'  => $config['active'],
                'simulatorIsEnabled' => $config['pagantis_simulator_is_enabled'],
                'thousandSeparator'  => $extraConfig['PAGANTIS_SIMULATOR_THOUSANDS_SEPARATOR'],
                'decimalSeparator'   => $extraConfig['PAGANTIS_SIMULATOR_DECIMAL_SEPARATOR'],
                'minAmount'          => $extraConfig['PAGANTIS_DISPLAY_MIN_AMOUNT'],
                'pagantisCSSSelector'        => $extraConfig['PAGANTIS_SIMULATOR_CSS_POSITION_SELECTOR'],
                'pagantisPriceSelector'      => $extraConfig['PAGANTIS_SIMULATOR_CSS_PRICE_SELECTOR'],
                'pagantisQuotesStart'        => $extraConfig['PAGANTIS_SIMULATOR_START_INSTALLMENTS'],
                'pagantisSimulatorType'      => $extraConfig['PAGANTIS_SIMULATOR_DISPLAY_TYPE'],
                'pagantisSimulatorSkin'      => $extraConfig['PAGANTIS_SIMULATOR_DISPLAY_SKIN'],
                'pagantisSimulatorPosition'  => $extraConfig['PAGANTIS_SIMULATOR_DISPLAY_CSS_POSITION'],
                'pagantisQuantitySelector'   => $extraConfig['PAGANTIS_SIMULATOR_CSS_QUANTITY_SELECTOR'],
                'pagantisTitle'              => $this->__($extraConfig['PAGANTIS_TITLE']),
                'pagantisSimulatorThousandSeparator' => $extraConfig['PAGANTIS_SIMULATOR_THOUSANDS_SEPARATOR'],
                'pagantisSimulatorDecimalSeparator' => $extraConfig['PAGANTIS_SIMULATOR_DECIMAL_SEPARATOR']
            ));

            if ($template->toHtml() == '') {
                $this->_allowSymlinks = true;
            }
            $template->setMethodTitle($title)->setMethodLabelAfterHtml($logoHtml);
        }
        parent::_construct();
    }
}
