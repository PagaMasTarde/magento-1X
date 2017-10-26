<?php

/**
 * Class DigitalOrigin_Pmt_Model_Iframe
 */
class DigitalOrigin_Pmt_Model_YesNo
{
    const YES = 1;
    const NO = 0;

    public function toOptionArray()
    {
        return array(
            array(
                'label' => Mage::helper('pmt')->__(' Yes'),
                'value' => self::YES,
            ),
            array(
                'label' => Mage::helper('pmt')->__(' No'),
                'value' => self::NO,
            )
        );
    }
}
