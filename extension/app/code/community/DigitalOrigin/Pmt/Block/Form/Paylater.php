<?php

class DigitalOrigin_Pmt_Block_Form_Paylater extends Mage_Payment_Block_Form
{
    protected function construct()
    {
        parent::_construct();
        $this->setTemplate('pmt/form/paylater.phtml');
    }
}
