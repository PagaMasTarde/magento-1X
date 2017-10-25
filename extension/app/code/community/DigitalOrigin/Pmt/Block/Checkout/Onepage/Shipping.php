<?php

/**
 * Extendemos / Rewrite del bloque estandar de Magento
 * para asegurarnos que el formulario del checkout puebla
 * la dirección, aunque el usuario no este logineado,
 * con la dirección del quote.
 *
 * Class Aplazame_Aplazame_Block_Checkout_Onepage_Shipping
 */
class Aplazame_Aplazame_Block_Checkout_Onepage_Shipping extends Mage_Checkout_Block_Onepage_Shipping
{
    /**
     * Return Sales Quote Address model
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
        if (is_null($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }

        return $this->_address;
    }
}
