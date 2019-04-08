<?php

/**
 * Class Pagantis_Pagantis_Model_Iframe
 */
class Pagantis_Pagantis_Model_YesNo
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
                'label' => Mage::helper('pagantis')->__(' Yes'),
                'value' => self::YES,
            ),
            array(
                'label' => Mage::helper('pagantis')->__(' No'),
                'value' => self::NO,
            )
        );
    }
}
