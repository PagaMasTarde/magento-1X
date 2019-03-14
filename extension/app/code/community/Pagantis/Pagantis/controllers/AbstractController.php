<?php
use Pagantis\ModuleUtils\Model\Log\LogEntry;

/**
 * Class AbstractController
 */
abstract class AbstractController extends Mage_Core_Controller_Front_Action
{
    /**
     * Concurrency Tablename
     */
    const PAGANTIS_CONCURRENCY_TABLE = 'CREATE TABLE `pagantis_cart_concurrency` (
  `id` INT NOT NULL ,
  `timestamp` INT NOT NULL ,
  PRIMARY KEY (`id`)
  )';

    /**
     * Pagantis Orders Tablename
     */
    const PAGANTIS_ORDERS_TABLE = 'CREATE TABLE `pagantis_orders` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `mg_order_id` varchar(50) NOT NULL, 
  `pagantis_order_id` varchar(50), 
  PRIMARY KEY (`id`),
  UNIQUE KEY (`mg_order_id`)
  )';

    /**
     * Pagantis Orders Tablename
     */
    const PAGANTIS_LOGS_TABLE = 'CREATE TABLE `pagantis_logs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `log` TEXT,
  `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
  )';

    /**
     * PAGANTIS_CODE
     */
    const PAGANTIS_CODE = 'pagantis';

    /**
     * @var array
     */
    public $modelTable = array(
        'pagantis/concurrency' => 'PAGANTIS_CONCURRENCY_TABLE',
        'pagantis/order' => 'PAGANTIS_ORDERS_TABLE',
        'pagantis/log' => 'PAGANTIS_LOGS_TABLE',
    );

    /**
     * @var integer $statusCode
     */
    protected $statusCode = 200;

    /**
     * @var string $errorMessage
     */
    protected $errorMessage = '';

    /**
     * @var string $errorDetail
     */
    protected $errorDetail = '';

    /**
     * @var string $headers
     */
    protected $headers;

    /**
     * @var string $format
     */
    protected $format = 'json';

    /**
     * Return a printable response of the request
     *
     * @return Mage_Core_Controller_Response_Http
     */
    public function response($extraOutput = array())
    {
        $response = $this->getResponse();

        $output = array();
        if (!empty($this->errorMessage)) {
            $output['errorMessage'] = $this->errorMessage;
        }
        if (!empty($this->errorDetail)) {
            $output['errorDetail'] = $this->errorDetail;
        }
        if (count($extraOutput)) {
            $output = array_merge($output, $extraOutput);
        }
        if ($this->format == 'json') {
            $output = json_encode($output);
            $response->setHeader('Content-Type', 'application/json');
            $response->setHeader('Content-Length', strlen($output));
        }

        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        $response->setHeader($protocol, $this->statusCode, $this->getHttpStatusCode($this->statusCode));
        $response->setBody($output);

        foreach ($this->headers as $key => $value) {
            $response->setHeader($key, $value);
        }
        return $response;
    }

    /**
     * Configure redirection
     *
     * @param bool $error
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function redirect($error = true)
    {
        if ($error) {
            return $this->_redirectUrl(Mage::getUrl($this->config['urlKO']));
        }
        return $this->_redirectUrl(Mage::getUrl($this->config['urlOK']));
    }

    /**
     * Return the HttpStatusCode description
     *
     * @param int $statusCode
     * @return string
     */
    public function getHttpStatusCode($statusCode = 200)
    {
        $httpStatusCodes = array(
            200 => "OK",
            201 => "Created",
            202 => "Accepted",
            400 => "Bad Request",
            401 => "Unauthorized",
            402 => "Payment Required",
            403 => "Forbidden",
            404 => "Not Found",
            405 => "Method Not Allowed",
            406 => "Not Acceptable",
            407 => "Proxy Authentication Required",
            408 => "Request Timeout",
            409 => "Conflict",
            429 => "Too Many Requests",
            500 => "Internal Server Error",
        );
        return isset($httpStatusCodes)? $httpStatusCodes[$statusCode] : $httpStatusCodes[200];
    }

    /**
     * Save log in SQL database
     *
     * @param $exception
     */
    public function saveLog(\Exception $exception)
    {
        try {
            $this->createTableIfNotExists('pagantis/log');
            $logEntry= new LogEntry();
            $logEntryJson = $logEntry->error($exception)->toJson();

            $model = Mage::getModel('pagantis/log');
            $model->setData(array(
                'log' => $logEntryJson,
            ));
            $model->save();
        } catch (\Exception $exception) {
            // Do nothing
        }
    }

    /**
     * Create pagantis_table if not exists
     *
     * @param null $model
     */
    public function createTableIfNotExists($model = null)
    {
        if (!is_null($model)) {
            try {
                $exists = Mage::getSingleton('core/resource')
                    ->getConnection('core_write')
                    ->isTableExists(Mage::getModel($model)->getResourceCollection()->getTable($model));

                $name = $this->modelTable[$model];
                $sql = constant("AbstractController::$name");
                if (!$exists) {
                    $resource = Mage::getSingleton('core/resource');
                    $writeConnection = $resource->getConnection('core_write');
                    $writeConnection->query($sql);
                }

            } catch (\Exception $exception) {
                // Do nothing
            }
        }
    }
}
