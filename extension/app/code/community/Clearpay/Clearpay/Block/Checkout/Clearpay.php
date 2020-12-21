<?php

/**
 * Class Clearpay_Clearpay_Block_Form_Clearpay
 */
class Clearpay_Clearpay_Block_Checkout_Clearpay extends Mage_Payment_Block_Form
{
    /**
     * Form constructor
     */
    protected function _construct()
    {
        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $config = Mage::getStoreConfig('payment/clearpay');
        $extraConfig = Mage::helper('clearpay/ExtraConfig')->getExtraConfig();
        $locale = substr(Mage::app()->getLocale()->getLocaleCode(), -2, 2);
        $checkoutSession = Mage::getModel('checkout/session');
        $quote = $checkoutSession->getQuote();
        $amount = $quote->getGrandTotal();
        $allowedCountries = json_decode($extraConfig['CLEARPAY_ALLOWED_COUNTRIES']);
        if ($config['clearpay_api_region'] === 'GB') {
            $allowedCountries = array('gb');
        }
        $promotedAmount =  0;
        $cart = Mage::getModel('checkout/cart')->getQuote();
        foreach ($cart->getAllVisibleItems() as $item) {
            $magentoProduct = $item->getProduct();
            $clearpayPromoted = $magentoProduct->getData("clearpay_promoted") ? 1 : 0;
            $productPrice = $item->getRowTotalInclTax();
            if ($clearpayPromoted) {
                $promotedAmount += $productPrice;
            }
        }

        if (in_array(strtolower($locale), $allowedCountries)) {
            $title = $this->__($extraConfig['CLEARPAY_TITLE']);
            $classCoreTemplate = Mage::getConfig()->getBlockClassName('core/template');

            $logoHtml = '';
            if ($config['active']) {
                $logoTemplate = new $classCoreTemplate;
                $logoTemplate->assign(array(
                    'locale'             => $locale,
                ));
                $logoHtml = $logoTemplate->setTemplate('clearpay/checkout/logo.phtml')->toHtml();

                if ($logoHtml == '') {
                    $logoTemplate->_allowSymlinks = true;
                    $logoHtml = $logoTemplate->setTemplate('clearpay/checkout/logo.phtml')->toHtml();
                }
            }

            $template = $this->setTemplate('clearpay/checkout/clearpay.phtml');
            $template->assign(array(
                'publicKey'          => $config['clearpay_merchant_id'],
                'amount'             => $amount,
                'promotedAmount'     => $promotedAmount,
                'locale'             => $locale,
                'country'            => $locale,
                'clearpayIsEnabled'  => $config['active'],
                'simulatorIsEnabled' => $config['clearpay_simulator_is_enabled'],
                'thousandSeparator'  => $extraConfig['CLEARPAY_SIMULATOR_THOUSANDS_SEPARATOR'],
                'decimalSeparator'   => $extraConfig['CLEARPAY_SIMULATOR_DECIMAL_SEPARATOR'],
                'minAmount'          => $config['clearpay_min_amount'],
                'maxAmount'          => $config['clearpay_max_amount'],
                'clearpayCSSSelector'        => $extraConfig['CLEARPAY_SIMULATOR_CSS_POSITION_SELECTOR_CHECKOUT'],
                'clearpayPriceSelector'      => $extraConfig['CLEARPAY_SIMULATOR_CSS_PRICE_SELECTOR_CHECKOUT'],
                'clearpayQuotesStart'        => $extraConfig['CLEARPAY_SIMULATOR_START_INSTALLMENTS'],
                'clearpaySimulatorType'      => $extraConfig['CLEARPAY_SIMULATOR_DISPLAY_TYPE_CHECKOUT'],
                'clearpaySimulatorSkin'      => $extraConfig['CLEARPAY_SIMULATOR_DISPLAY_SKIN'],
                'clearpaySimulatorPosition'  => $extraConfig['CLEARPAY_SIMULATOR_DISPLAY_CSS_POSITION'],
                'clearpayQuantitySelector'   => $extraConfig['CLEARPAY_SIMULATOR_CSS_QUANTITY_SELECTOR'],
                'clearpayTitle'              => $this->__($extraConfig['CLEARPAY_TITLE'])
            ));

            if ($template->toHtml() == '') {
                $this->_allowSymlinks = true;
            }
            $template->setMethodTitle($title)->setMethodLabelAfterHtml($logoHtml);
        }
        parent::_construct();
    }
}
