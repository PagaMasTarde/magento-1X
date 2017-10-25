<?php

/**
 * @method Aplazame_Aplazame_Model_Payment getMethod()
 */
class Aplazame_Aplazame_Block_Payment_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('aplazame/payment/form.phtml');
    }

    public function getInstructions()
    {
        return trim($this->getMethod()->getConfigData('instructions'));
    }

    public function getQuote()
    {
        return $this->getMethod()->getCheckout()->getQuote();
    }
}
