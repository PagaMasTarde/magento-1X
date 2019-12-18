<?php

namespace Test\Controllers;

use Httpful\Request;
use Httpful\Mime;
use Test\Magento19Test;

/**
 * Class Controller19Test
 * @package Test
 *
 * @group magento-controllers-19
 */
class Controller19Test extends Magento19Test
{
    /**
     * log route
     */
    const LOG_FOLDER = '/pagantis/log/download';

    /**
     * config route
     */
    const CONFIG_FOLDER = '/pagantis/config/';

    protected $configs = array(
        "PAGANTIS_TITLE",
        "PAGANTIS_SIMULATOR_DISPLAY_TYPE",
        "PAGANTIS_SIMULATOR_DISPLAY_SKIN",
        "PAGANTIS_SIMULATOR_DISPLAY_POSITION",
        "PAGANTIS_SIMULATOR_START_INSTALLMENTS",
        "PAGANTIS_SIMULATOR_CSS_POSITION_SELECTOR",
        "PAGANTIS_SIMULATOR_DISPLAY_CSS_POSITION",
        "PAGANTIS_SIMULATOR_CSS_PRICE_SELECTOR",
        "PAGANTIS_SIMULATOR_CSS_QUANTITY_SELECTOR",
        "PAGANTIS_FORM_DISPLAY_TYPE",
        "PAGANTIS_DISPLAY_MIN_AMOUNT",
        "PAGANTIS_URL_OK",
        "PAGANTIS_URL_KO",
    );

    /**
     * Test testLogDownload
     */
    public function testLogDownload()
    {
        $logUrl = $this->magentoUrl.self::LOG_FOLDER.'?secret='.$this->configuration['secretKey'];
        $response = Request::get($logUrl)->expects('json')->send();
        $this->assertEquals(2, count($response->body));
        $this->quit();
    }

    /**
     * Test testSetConfig
     */
    public function testSetConfig()
    {
        $notifyUrl = $this->magentoUrl.self::CONFIG_FOLDER.'post?secret='.$this->configuration['secretKey'];
        $body = array('PAGANTIS_TITLE' => 'changed');
        $response = Request::post($notifyUrl)
            ->body($body, Mime::FORM)
            ->expectsJSON()
            ->send();
        $this->assertEquals('changed', $response->body->PAGANTIS_TITLE);
        $this->quit();
    }

    /**
     * Test testGetConfig
     */
    public function testGetConfigs()
    {
        $notifyUrl = $this->magentoUrl.self::CONFIG_FOLDER.'get?secret='.$this->configuration['secretKey'];
        $response = Request::get($notifyUrl)->expects('json')->send();

        foreach ($this->configs as $config) {
            $this->assertArrayHasKey($config, (array) $response->body);
        }
        $this->quit();
    }
}
