<?php

/**
 * Class DigitalOrigin_Pmt_Block_Form_Paylater
 */
class DigitalOrigin_Pmt_Block_Adminhtml_LogoField extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    /**
     * Return header comment part of html for payment solution
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    protected function _getHeaderCommentHtml($element)
    {
        return '<div class="adminLogo">
              <p class="description">Paga+Tarde es una plataforma de financiación online.</p>
              <p class="description"><a href="https://bo.pagamastarde.com" target="_blank">Login al panel de Paga+Tarde</a>&nbsp;
              <a href="http://docs.pagamastarde.com/" target="_blank">Documentación</a></p></div>';
    }
}
