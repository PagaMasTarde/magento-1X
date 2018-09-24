<?php

require_once('lib/DigitalOrigin/autoload.php');
require_once('app/code/community/DigitalOrigin/Pmt/controllers/BaseController.php');

/**
 * Class DigitalOrigin_Pmt_LogController
 */
class DigitalOrigin_Pmt_LogController extends BaseController
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

        $result = array();
        $logDir  = Mage::getBaseDir('var') . DIRECTORY_SEPARATOR . 'log';
        if (is_dir($logDir)) {
            $pmtLogFile = $logDir . DIRECTORY_SEPARATOR . 'pmt.log';
            if (file_exists($pmtLogFile)) {
                $result['pmt'] = file_get_contents($pmtLogFile);
            }
        }
        echo file_get_contents($pmtLogFile);die;
        $this->message = $result;
        $this->code = 200;
        return $this->response();
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
