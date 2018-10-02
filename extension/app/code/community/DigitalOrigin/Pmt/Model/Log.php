<?php
/**
 * Created by PhpStorm.
 * User: rmarin
 * Date: 2/10/18
 * Time: 9:40
 */
class DigitalOrigin_Pmt_Model_Log extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('pmt/log');
    }
}