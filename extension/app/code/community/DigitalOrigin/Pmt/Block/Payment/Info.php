<?php


class Aplazame_Aplazame_Block_Payment_Info extends Mage_Payment_Block_Info
{
    protected function _toHtml()
    {
        $html = '<a href="https://aplazame.com">Aplazame</a>';
        return $html;
    }
}
