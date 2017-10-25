<?php

class Aplazame_Aplazame_Block_AdminHtml_ProductsCampaigns extends Aplazame_Aplazame_Block_AdminHtml_ProductCampaigns
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('aplazame/productsCampaigns.phtml');
    }
}
