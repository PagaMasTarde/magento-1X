<?php
/**
 * Created by PhpStorm.
 * User: rmarin
 * Date: 2/10/18
 * Time: 9:54
 */
class DigitalOrigin_Pmt_Model_Resource_Log extends Mage_Core_Model_Resource_Db_Abstract{
    protected function _construct()
    {
        $idFieldName = 'id'; // whatever the column is named.
        $this->_init('pmt/log', $idFieldName);
    }
}