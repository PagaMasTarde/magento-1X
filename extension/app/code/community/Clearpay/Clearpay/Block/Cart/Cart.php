<?php

/**
 * Class Clearpay_Clearpay_Block_Cart_Message
 */
class Clearpay_Clearpay_Block_Cart_Message extends Mage_Catalog_Block_Product_View
{
    /**
     * Form constructor
     */
    protected function _construct()
    {
        die("sip");
        $config = Mage::getStoreConfig('payment/clearpay');
        $extraConfig = Mage::helper('clearpay/ExtraConfig')->getExtraConfig();
        $locale = substr(Mage::app()->getLocale()->getLocaleCode(), -2, 2);
        $localeISOCode = Mage::app()->getLocale()->getLocaleCode();
        $allowedCountries = json_decode($extraConfig['ALLOWED_COUNTRIES']);
        $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
        //$categoryRestriction = $this->isProductRestricted($config['clearpay_exclude_category']);
        $categoryRestriction = false;
        if (in_array(strtoupper($locale), $allowedCountries) &&
            $config['active'] === '1' &&
            !empty($config['clearpay_merchant_id']) &&
            !empty($config['clearpay_secret_key']) &&
            !$categoryRestriction
        ) {
            $this->assign(
                array(
                    'SDK_URL' => self::CLEARPAY_JS_CDN_URL,
                    'ISO_COUNTRY_CODE' => $localeISOCode,
                    'CURRENCY' => $currency,
                    'CLEARPAY_MIN_AMOUNT' => $config['clearpay_min_amount'],
                    'CLEARPAY_MAX_AMOUNT' => $config['clearpay_max_amount'],
                    'PRICE_SELECTOR' => $extraConfig['PRICE_SELECTOR'],
                    'PRICE_SELECTOR_CONTAINER' => $extraConfig['PRICE_SELECTOR_CONTAINER']
                )
            );

            // check symlinks
            $classCoreTemplate = Mage::getConfig()->getBlockClassName('core/template');
            $simulatorTemplate = new $classCoreTemplate;
            $simulator = $simulatorTemplate->setTemplate('clearpay/cart/cart.phtml')->toHtml();
            if ($simulator == '') {
                $this->_allowSymlinks = true;
            }
        }
        parent::_construct();
    }
}
