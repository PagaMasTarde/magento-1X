<?php

class Aplazame_Aplazame_Block_Checkout_Cart_Widget Extends Mage_Core_Block_Template
{
    /**
     * @var Mage_Sales_Model_Quote $_product
     */
    protected $_quote;


    /**
     * Devuelve el quote actual
     * @return Mage_Sales_Model_Quote|mixed
     */
    public function getQuote()
    {
        if(!$this->_quote)
        {
            $this->_quote = $quote = Mage::getSingleton('checkout/session')->getQuote();
        }

        return $this->_quote;
    }

    /**
     * Devuelve el final price del producto
     * @return float
     */
    public function getTotal()
    {
        if($this->getQuote() instanceof Mage_Sales_Model_Quote)
        {
            $total = $this->getQuote()->getGrandTotal();
        } else {
            $total = 0;
        }

        return $total;
    }

    /**
     * Solo renderizamos si el modulo esta activo en la tienda actual
     *
     * @return string
     */
    public function _toHtml()
    {
        if(Mage::helper('aplazame')->isEnabled())
        {
            return parent::_toHtml();
        }

        return '';
    }

}
