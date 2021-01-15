<?php

/**
 * Class Pagantis_Pagantis_Model_Environment
 */
class Pagantis_Pagantis_Model_Environment
{
    /**
     * SANDBOX
     */
    const SANDBOX = 'sandbox';

    /**
     * PRODUCTION
     */
    const PRODUCTION = 'production';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'label' => Mage::helper('pagantis')->__(' Sandbox'),
                'value' => self::SANDBOX,
            ),
            array(
                'label' => Mage::helper('pagantis')->__(' Production'),
                'value' => self::PRODUCTION,
            )
        );
    }
}
