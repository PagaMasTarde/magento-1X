<?php

class DigitalOrigin_Pmt_Block_Info_Paylater extends Mage_Payment_Block_Info
{
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }

        $data = array();

        $transport = parent::_prepareSpecificInformation($transport);

        return $transport->setData(array_merge($data, $transport->getData()));
    }
}
