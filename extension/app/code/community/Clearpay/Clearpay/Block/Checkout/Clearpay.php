<?php

/**
 * Class Clearpay_Clearpay_Block_Form_Clearpay
 */
class Clearpay_Clearpay_Block_Checkout_Clearpay extends Mage_Payment_Block_Form
{
    /**
     * JS CDN URL
     */
    const CLEARPAY_JS_CDN_URL = 'https://js.sandbox.afterpay.com/afterpay-1.x.js';

    /**
     * Form constructor
     */
    protected function _construct()
    {
        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $config = Mage::getStoreConfig('payment/clearpay');
        $extraConfig = Mage::helper('clearpay/ExtraConfig')->getExtraConfig();
        $locale = substr(Mage::app()->getLocale()->getLocaleCode(), -2, 2);
        $localeISOCode = Mage::app()->getLocale()->getLocaleCode();
        $checkoutSession = Mage::getModel('checkout/session');
        $quote = $checkoutSession->getQuote();
        $amount = $quote->getGrandTotal();
        $allowedCountries = json_decode($extraConfig['ALLOWED_COUNTRIES']);
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

        if (in_array(strtoupper($locale), $allowedCountries)) {
            $title = $this->__($extraConfig['CLEARPAY_TITLE']);
            $classCoreTemplate = Mage::getConfig()->getBlockClassName('core/template');
            $localConfigs = array(
                'ES' => array(
                  'currency' => 'EUR',
                  'symbol' => '€'
                ),
                'GB' => array(
                  'currency' => 'GBP',
                  'symbol' => '£'
                ),
                'US' => array(
                  'currency' => 'USD',
                  'symbol' => '$'
                ),
            );
            $currency = 'EUR';
            $currencySymbol = "€";
            if (isset($localConfigs[$config['clearpay_api_region']])) {
                $currency = $localConfigs[$config['clearpay_api_region']]['currency'];
                $currencySymbol = $localConfigs[$config['clearpay_api_region']]['symbol'];
            }

            $amountWithCurrency = $this->parseAmount($amount/4) . $currencySymbol;
            if ($currency === 'GBP') {
                $amountWithCurrency = $currencySymbol. $this->parseAmount($amount/4);
            }
            $checkoutText = $this->__('Or 4 interest-free payments of') . ' ' . $amountWithCurrency . ' ';
            $checkoutText .= $this->__('with');

            $logoHtml = '';
            if ($config['active']) {
                $logoTemplate = new $classCoreTemplate;
                $logoTemplate->assign(array(
                    'TITLE' => (string) $checkoutText,
                ));
                $logoHtml = $logoTemplate->setTemplate('clearpay/checkout/logo.phtml')->toHtml();

                if ($logoHtml == '') {
                    $logoTemplate->_allowSymlinks = true;
                    $logoHtml = $logoTemplate->setTemplate('clearpay/checkout/logo.phtml')->toHtml();
                }
            }

            $template = $this->setTemplate('clearpay/checkout/clearpay.phtml');
            $template->assign(array(
                'SDK_URL' => self::CLEARPAY_JS_CDN_URL,
                'MOREINFO_HEADER' => $this->__('Instant approval decision - 4 interest-free payments of')
                    . ' ' . $amountWithCurrency,
                'MOREINFO_ONE' => $this->__('You will be redirected to Clearpay website to fill out your payment information.')
                    . ' ' .$this->__('You will be redirected to our site to complete your order. Please note: ')
                    . ' ' . $this->__('Clearpay can only be used as a payment method for orders with a shipping')
                    . ' ' . $this->__('and billing address within the UK.'),
                'TOTAL_AMOUNT' => $this->parseAmount($amount),
                'ISO_COUNTRY_CODE' => $localeISOCode,
                'CURRENCY' => $currency,
                'TERMS_AND_CONDITIONS' => $this->__('Terms and conditions'),
                'TERMS_AND_CONDITIONS_LINK' => $this->__('https://www.clearpay.co.uk/en-GB/terms-of-service')
            ));

            if ($template->toHtml() == '') {
                $this->_allowSymlinks = true;
            }
            $template->setMethodTitle($title)->setMethodLabelAfterHtml($logoHtml);
        }
        parent::_construct();
    }

    /**
     * @param null $amount
     * @return string
     */
    public function parseAmount($amount = null)
    {
        return number_format(
            round($amount, 2, PHP_ROUND_HALF_UP),
            2,
            '.',
            ''
        );
    }
}
