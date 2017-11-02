<?php

/**
 * Class DigitalOrigin_Pmt_Model_Iframe
 */
class DigitalOrigin_Pmt_Model_ProductSimulator
{
    /**
     * YES
     */
    const YES = 1;

    /**
     * NO
     */
    const NO = 0;

    /**
     * @return array
     */
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
