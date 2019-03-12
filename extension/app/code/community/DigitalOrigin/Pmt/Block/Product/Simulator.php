<?php

/**
 * Class DigitalOrigin_Pmt_Block_Product_Simulator
 */
class DigitalOrigin_Pmt_Block_Product_Simulator extends Mage_Catalog_Block_Product_View
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
        $config      = Mage::getStoreConfig('payment/paylater');
        $extraConfig = Mage::helper('pmt/ExtraConfig')->getExtraConfig();

        $this->assign(
            array(
                'amount'                => Mage::app()->getStore()->convertPrice($this->getProduct()->getFinalPrice()),
                'pmtIsEnabled'          => $config['active'],
                'pmtPublicKey'          => $config['pmt_public_key'],
                'pmtSimulatorIsEnabled' => $config['pmt_simulator_is_enabled'],
                'pmtMinAmount'          => $extraConfig['PMT_DISPLAY_MIN_AMOUNT'],
                'pmtCSSSelector'        => $extraConfig['PMT_SIMULATOR_CSS_POSITION_SELECTOR'],
                'pmtPriceSelector'      => $extraConfig['PMT_SIMULATOR_CSS_PRICE_SELECTOR'],
                'pmtQuotesStart'        => $extraConfig['PMT_SIMULATOR_START_INSTALLMENTS'],
                'pmtSimulatorType'      => $extraConfig['PMT_SIMULATOR_DISPLAY_TYPE'],
                'pmtSimulatorSkin'      => $extraConfig['PMT_SIMULATOR_DISPLAY_SKIN'],
                'pmtSimulatorPosition'  => $extraConfig['PMT_SIMULATOR_DISPLAY_CSS_POSITION'],
                'pmtQuantitySelector'   => $extraConfig['PMT_SIMULATOR_CSS_QUANTITY_SELECTOR'],
                'pmtTitle'              => $extraConfig['PMT_TITLE'],
            )
        );

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
