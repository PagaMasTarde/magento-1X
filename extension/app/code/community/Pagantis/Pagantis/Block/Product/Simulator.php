<?php

/**
 * Class Pagantis_Pagantis_Block_Product_Simulator
 */
class Pagantis_Pagantis_Block_Product_Simulator extends Mage_Catalog_Block_Product_View
{
    /**
     * @var Mage_Catalog_Model_Product $_product
     */
    protected $_product;

    /**
     * Form constructor
     */
    protected function _construct()
    {
        $config         = Mage::getStoreConfig('payment/pagantis');
        $extraConfig    = Mage::helper('pagantis/ExtraConfig')->getExtraConfig();
        $locale         = substr(Mage::app()->getLocale()->getLocaleCode(), -2, 2);
        $amount         = Mage::app()->getStore()->convertPrice($this->getProduct()->getFinalPrice());
        $allowedCountries = unserialize($extraConfig['PAGANTIS_ALLOWED_COUNTRIES']);
        $pagantisPromoted = $this->getProduct()->getData("pagantis_promoted") ? 1 : 0;
        $pagantisMinAmount = $extraConfig['PAGANTIS_DISPLAY_MIN_AMOUNT'];
        $pagantisMaxAmount = $extraConfig['PAGANTIS_DISPLAY_MAX_AMOUNT'];

        if (in_array(strtolower($locale), $allowedCountries) &&
            $amount >= $pagantisMinAmount &&
            ($amount <= $pagantisMaxAmount || $pagantisMaxAmount == '0') && $config['active'] === '1') {
            $this->assign(
                array(
                    'locale'                     => $locale,
                    'country'                    => $locale,
                    'amount'                     => $amount,
                    'promoted'                   => $pagantisPromoted,
                    'pagantisPromotedMessage'    => $extraConfig['PAGANTIS_PROMOTION_MESSAGE'],
                    'pagantisIsEnabled'          => $config['active'],
                    'pagantisPublicKey'          => $config['pagantis_public_key'],
                    'pagantisSimulatorIsEnabled' => $config['pagantis_simulator_is_enabled'],
                    'pagantisMinAmount'          => $pagantisMinAmount,
                    'pagantisMaxAmount'          => $pagantisMaxAmount,
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
                )
            );

            // check symlinks
            $classCoreTemplate = Mage::getConfig()->getBlockClassName('core/template');
            $simulatorTemplate = new $classCoreTemplate;
            $simulator = $simulatorTemplate->setTemplate('pagantis/product/simulator.phtml')->toHtml();

            if ($simulator == '') {
                $this->_allowSymlinks = true;
            }
        }
        parent::_construct();
    }

    /**
     * Devuelve el current product cuando estamos en ficha de producto
     *
     * @return Mage_Catalog_Model_Product|mixed
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = Mage::registry('current_product');
        }

        return $this->_product;
    }
}
