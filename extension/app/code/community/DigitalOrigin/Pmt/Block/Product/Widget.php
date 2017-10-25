<?php

class Aplazame_Aplazame_Block_Product_Widget Extends Mage_Core_Block_Template
{
    /**
     * @var Mage_Catalog_Model_Product $_product
     */
    protected $_product;


    /**
     * Devuelve el current product cuando estamos en ficha de producto
     * @return Mage_Catalog_Model_Product|mixed
     */
    public function getProduct()
    {
        if(!$this->_product)
        {
            $this->_product = Mage::registry('current_product');
        }

        return $this->_product;
    }

    /**
     * Devuelve el final price del producto
     * @return float
     */
    public function getFinalPrice()
    {
        if($this->getProduct() instanceof Mage_Catalog_Model_Product)
        {
            $total = Mage::app()->getStore()->convertPrice($this->getProduct()->getFinalPrice());
        } else {
            $total = 0;
        }

        return $total;
    }

    /**
     * Solo renderizamos si tenemos producto,
     * y si el modulo esta activo en la tienda actual
     * si no hay producto no renderizamos nada (empty string).
     *
     * @return string
     */
    public function _toHtml()
    {
        if(Mage::helper('aplazame')->isEnabled() && $this->getProduct() instanceof Mage_Catalog_Model_Product)
        {
            return parent::_toHtml();
        }

        return '';
    }
}
