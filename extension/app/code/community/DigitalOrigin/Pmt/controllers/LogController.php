<?php

require_once('lib/DigitalOrigin/autoload.php');
require_once('app/code/community/DigitalOrigin/Pmt/controllers/BaseController.php');

/**
 * Class DigitalOrigin_Pmt_LogController
 */
class DigitalOrigin_Pmt_LogController extends BaseController
{
    /**
     * @var string $message
     */
    protected $message;

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
            $result = array('timestamp' => time(), 'result' => 'Access Forbidden');
            return $this->response($result, array(),403);
        }

        $result = array('timestamp' => time(), result => array());
        $logDir  = Mage::getBaseDir('var') . DIRECTORY_SEPARATOR . 'log';
        if (is_dir($logDir)) {
            $pmtLogFile = $logDir . DIRECTORY_SEPARATOR . 'pmt.log';
            $prepmtLogFile = $logDir . DIRECTORY_SEPARATOR . 'pre-pmt.log';
            if (file_exists($pmtLogFile)) {
                $result['result']['pmt'] = file_get_contents($pmtLogFile);
            }
            if (file_exists($prepmtLogFile)) {
                $result['result']['pre-pmt'] = file_get_contents($prepmtLogFile);
            }
        }

        return $this->response($result, array(), 200);
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
