<?php

/**
 * Class Pagantis_Pagantis_Model_ApiRegion
 */
class Pagantis_Pagantis_Model_ApiRegion
{
    /**
     * EU
     */
    const EU = 'ES';

    /**
     * GB
     */
    const GB = 'GB';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'label' => Mage::helper('pagantis')->__(' Europe'),
                'value' => self::EU,
            ),
            array(
                'label' => Mage::helper('pagantis')->__(' United Kingdom'),
                'value' => self::GB,
            )
        );
    }
}
