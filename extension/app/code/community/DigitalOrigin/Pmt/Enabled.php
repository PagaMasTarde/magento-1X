<?php

namespace DigitalOrigin\Pmt\Model\Admin;

/**
 * Class DigitalOrigin_Pmt_Model_Enabled
 */
class DigitalOrigin_Pmt_Model_Enabled
{
    const PRODUCTION = 1;
    const TESTING = 0;

    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'Production',
                'label' => self::PRODUCTION,
            ),
            array(
                'value' => 'Testing',
                'label' => self::TESTING,
            )
        );
    }
}
