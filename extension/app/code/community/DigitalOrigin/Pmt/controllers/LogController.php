<?php

require_once('lib/DigitalOrigin/autoload.php');
require_once('app/code/community/DigitalOrigin/Pmt/controllers/AbstractController.php');

/**
 * Class DigitalOrigin_Pmt_LogController
 */
class DigitalOrigin_Pmt_LogController extends AbstractController
{
    /**
     * @var bool $error
     */
    protected $error = false;

    /**
     * Controller download method:
     *
     * @return Mage_Core_Controller_Response_Http
     * @throws Zend_Controller_Request_Exception
     */
    public function downloadAction()
    {
        if (!$this->authorize()) {
            $this->message = 'Access Forbidden';
            $this->code = 403;
            return $this->response();
        }

        $limit = Mage::app()->getRequest()->getParam('limit');
        $from  = Mage::app()->getRequest()->getParam('from');
        $to    = Mage::app()->getRequest()->getParam('to');
        if (is_numeric($from) && is_numeric($to)) {
            $sqlPart = ' and DATE_FORMAT(createdAt, \'%Y%m%d\') between ' . $from . ' and ' . $to;
        }
        $sql   = 'select log from ' . self::PMT_LOGS_TABLE
            . ' where 1=1 ' . $sqlPart . ' order by id desc limit '
            . (($limit && is_numeric($limit)) ? $limit : 200);

        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $result = $conn->fetchAll($sql);
        $output = array();
        foreach ($result as $log) {
            $output[] = json_decode($log['log']);
        }
        $this->statusCode = 200;
        return $this->response($output);
    }

    /**
     * Check if private key is provided and is equal than setted in backoffice
     *
     * @return bool
     * @throws Zend_Controller_Request_Exception
     */
    public function authorize()
    {
        $moduleConfig = Mage::getStoreConfig('payment/paylater');
        $env = $moduleConfig['PAYLATER_PROD'] ? 'PROD' : 'TEST';
        $privateKey = $moduleConfig['PAYLATER_PRIVATE_KEY_'.$env];
        if ((Mage::app()->getRequest()->getParam('secret') == $privateKey ||
            Mage::app()->getRequest()->getHeader('secret') == $privateKey)
            && !empty($privateKey)) {
            return true;
        }

        return false;
    }


}
