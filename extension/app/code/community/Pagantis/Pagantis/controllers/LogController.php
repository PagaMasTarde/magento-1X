<?php

require_once(__DIR__.'/../../../../../../lib/Pagantis/autoload.php');
require_once(__DIR__.'/AbstractController.php');

/**
 * Class Pagantis_Pagantis_LogController
 */
class Pagantis_Pagantis_LogController extends AbstractController
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
            $this->errorMessage = 'Access Forbidden';
            $this->statusCode = 403;
            return $this->response();
        }

        $limit = Mage::app()->getRequest()->getParam('limit');
        $from  = Mage::app()->getRequest()->getParam('from');
        $to    = Mage::app()->getRequest()->getParam('to');
        if (is_numeric($from) && is_numeric($to)) {
            $sqlPart = ' and DATE_FORMAT(createdAt, \'%Y%m%d\') between ' . $from . ' and ' . $to;
        }
        $sql   = 'select log, createdAt from pagantis_log where 1=1 ' . $sqlPart . ' order by id desc limit '
            . (($limit && is_numeric($limit)) ? $limit : 200);

        $conn = Mage::getSingleton('core/resource')->getConnection('core_write');
        $result = $conn->fetchAll($sql);

        $output = array();
        foreach ($result as $key => $log) {
            $output[$key]['log'] = json_decode($log['log']);
            $output[$key]['timestamp'] = $log['createdAt'];
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
        $moduleConfig = Mage::getStoreConfig('payment/pagantis');
        $privateKey = $moduleConfig['pagantis_private_key'];
        if ((Mage::app()->getRequest()->getParam('secret') == $privateKey ||
            Mage::app()->getRequest()->getHeader('secret') == $privateKey)
            && !empty($privateKey)) {
            return true;
        }

        return false;
    }


}
