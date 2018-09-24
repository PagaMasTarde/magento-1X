<?php

/**
 * Class BaseController
 */
class BaseController extends Mage_Core_Controller_Front_Action
{
    /**
     * @var integer $code
     */
    protected $code;

    /**
     * @var string $message
     */
    protected $message;

    /**
     * Concurrency Tablename
     */
    const CONCURRENCY_TABLE = 'pmt_cart_concurrency';

    /**
     * Pmt Orders Tablename
     */
    const PMT_ORDERS_TABLE = 'pmt_orders';

    /**
     * Code
     */
    const CODE = 'paylater';

    /**
     * EXCEPTION RESPONSES
     */
    const CC_ERR_MSG = 'Unable to block resource';
    const CC_NO_MERCHANT_ORDER_ID = 'Merchant Order Id not found';
    const CC_NO_VALIDATE ='Validation in progress, try again later';

    const GMO_ERR_MSG = 'Merchant Order Not Found';
    const GPOI_ERR_MSG = 'Pmt Order Not Found';
    const GPOI_NO_ORDERID = 'We can not get the PagaMasTarde identification in database.';
    const GPO_ERR_MSG = 'Unable to get Order';
    const COS_ERR_MSG = 'Order status is not authorized';
    const COS_WRONG_STATUS = 'Invalid Pmt status';
    const CMOS_ERR_MSG = 'Merchant Order status is invalid';
    const CMOS_ALREADY_PROCESSED = 'Cart already processed.';
    const VA_ERR_MSG = 'Amount conciliation error';
    const VA_WRONG_AMOUNT = 'Wrong order amount';
    const PMO_ERR_MSG = 'Unknown Error';
    const CPO_ERR_MSG = 'Order not confirmed';
    const CPO_OK_MSG = 'Order confirmed';

    /**
     * Return a printable response of the request
     *
     * @param string $result
     * @param array  $headers
     * @param string  $format
     *
     * @return Mage_Core_Controller_Response_Http
     */
    public function response($format = 'json', $headers = array()) {
        $response = $this->getResponse();

        $output = json_encode(array(
            'date' => date("Y-m-d H:i:s"),
            'message' => $this->message,
        ));


        if($format == 'json') {
            $response->setHeader('Content-Type', 'application/json');
            $response->setHeader('Content-Length', strlen($output));
        }

        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        $response->setHeader($protocol, $this->code, $this->getHttpStatusCode($this->code));
        $response->setBody($output);

        foreach ($headers as $key => $value) {
            $response->setHeader($key, $value);
        }
        return $response;
    }

    /**
     * @param int $statusCode
     * @return string
     */
    public function getHttpStatusCode($statusCode = 200) {
        $httpStatusCodes = array(
            100 => "Continue",
            101 => "Switching Protocols",
            102 => "Processing",
            200 => "OK",
            201 => "Created",
            202 => "Accepted",
            203 => "Non-Authoritative Information",
            204 => "No Content",
            205 => "Reset Content",
            206 => "Partial Content",
            207 => "Multi-Status",
            300 => "Multiple Choices",
            301 => "Moved Permanently",
            302 => "Found",
            303 => "See Other",
            304 => "Not Modified",
            305 => "Use Proxy",
            306 => "(Unused)",
            307 => "Temporary Redirect",
            308 => "Permanent Redirect",
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
            410 => "Gone",
            411 => "Length Required",
            412 => "Precondition Failed",
            413 => "Request Entity Too Large",
            414 => "Request-URI Too Long",
            415 => "Unsupported Media Type",
            416 => "Requested Range Not Satisfiable",
            417 => "Expectation Failed",
            418 => "I'm a teapot",
            419 => "Authentication Timeout",
            420 => "Enhance Your Calm",
            422 => "Unprocessable Entity",
            423 => "Locked",
            424 => "Failed Dependency",
            425 => "Unordered Collection",
            426 => "Upgrade Required",
            428 => "Precondition Required",
            429 => "Too Many Requests",
            431 => "Request Header Fields Too Large",
            444 => "No Response",
            449 => "Retry With",
            450 => "Blocked by Windows Parental Controls",
            451 => "Unavailable For Legal Reasons",
            494 => "Request Header Too Large",
            495 => "Cert Error",
            496 => "No Cert",
            497 => "HTTP to HTTPS",
            499 => "Client Closed Request",
            500 => "Internal Server Error",
            501 => "Not Implemented",
            502 => "Bad Gateway",
            503 => "Service Unavailable",
            504 => "Gateway Timeout",
            505 => "HTTP Version Not Supported",
            506 => "Variant Also Negotiates",
            507 => "Insufficient Storage",
            508 => "Loop Detected",
            509 => "Bandwidth Limit Exceeded",
            510 => "Not Extended",
            511 => "Network Authentication Required",
            598 => "Network read timeout error",
            599 => "Network connect timeout error",
        );
        return isset($httpStatusCodes)? $httpStatusCodes[$statusCode] : $httpStatusCodes[200];
    }

    public function saveLog(Exception $e = null , $data = array())
    {
        $data = array_merge($data, array(
            'timestamp' => time(),
            'date' => date("Y-m-d H:i:s"),
            'message' => $e->getMessage(),
            'class' => get_class($this),
            'method' => __FUNCTION__,
            'line' => $e->getLine(),
        ));
        Mage::log(
            json_encode($data),
            null,
            'pmt.log',
            true
        );
    }
}
