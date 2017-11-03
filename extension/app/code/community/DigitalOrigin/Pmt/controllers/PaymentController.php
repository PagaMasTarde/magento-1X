<?php

/**
 * Class DigitalOrigin_Pmt_PaymentController
 */
class DigitalOrigin_Pmt_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * Index action
     */
    public function indexAction()
    {
        $salesOrder = Mage::getModel('sales/order');
        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $checkoutSession = Mage::getSingleton('checkout/session');
        /** @var Mage_Customer_Model_Session $customerSession */
        $customerSession = Mage::getSingleton('customer/session');

        $orderId = $checkoutSession->getLastRealOrderId();
        $customer = $customerSession->getCustomer();
        $customer->getAttributes();
        $order = $salesOrder->loadByIncrementId($orderId);

        echo '<pre>';
        var_dump(
            $orderId,
            $customer->getEmail()
        );

        die();
    }

    /**
     * Process Post Request
     */
    public function postProcess()
    {
        /** @var Cart $cart */
        $cart = $this->context->cart;
        if (!$cart->id) {
            Tools::redirect('index.php?controller=order');
        }
        /** @var Customer $customer */
        $customer = $this->context->customer;
        $link = $this->context->link;
        $query = array(
            'id_cart' => $cart->id,
            'key' => $cart->secure_key,
        );
        $currency = new Currency($cart->id_currency);
        $currencyIso = $currency->iso_code;
        $cancelUrl = $link->getPageLink('order');
        $paylaterProd = Configuration::get('PAYLATER_PROD');
        $paylaterMode = $paylaterProd == 1 ? 'PROD' : 'TEST';
        $paylaterPublicKey = Configuration::get('PAYLATER_PUBLIC_KEY_'.$paylaterMode);
        $paylaterPrivateKey = Configuration::get('PAYLATER_PRIVATE_KEY_'.$paylaterMode);
        $iframe = Configuration::get('PAYLATER_IFRAME');
        $includeSimulator = Configuration::get('PAYLATER_ADD_SIMULATOR');
        $okUrl = $link->getModuleLink('paylater', 'notify', $query);
        $shippingAddress = new Address($cart->id_address_delivery);
        $billingAddress = new Address($cart->id_address_invoice);
        $discount = Configuration::get('PAYLATER_DISCOUNT');
        $link = Tools::getHttpHost(true).__PS_BASE_URI__;
        $spinner = $link . ('modules/paylater/views/img/spinner.gif');
        $css = 'https://shopper.pagamastarde.com/css/paylater-modal.min.css';
        $prestashopCss = 'https://shopper.pagamastarde.com/css/paylater-prestashop.min.css';
        $prestashopObjectModule = new \ShopperLibrary\ObjectModule\PrestashopObjectModule();
        $prestashopObjectModule
            ->setPublicKey($paylaterPublicKey)
            ->setPrivateKey($paylaterPrivateKey)
            ->setCurrency($currencyIso)
            ->setDiscount($discount)
            ->setOkUrl($okUrl)
            ->setNokUrl($cancelUrl)
            ->setIFrame($iframe)
            ->setCallbackUrl($okUrl)
            ->setCancelledUrl($cancelUrl)
            ->setIncludeSimulator($includeSimulator)
            ->setCart(CartExport::export($cart))
            ->setCustomer(CustomerExport::export($customer))
            ->setPsShippingAddress(AddressExport::export($shippingAddress))
            ->setPsBillingAddress(AddressExport::export($billingAddress))
            ->setMetadata(array(
                'ps' => _PS_VERSION_,
                'pmt' => $this->module->version,
                'php' => phpversion(),
            ))
        ;
        $shopperClient = new \ShopperLibrary\ShopperClient(PAYLATER_SHOPPER_URL);
        $shopperClient->setObjectModule($prestashopObjectModule);
        $paymentForm = $shopperClient->getPaymentForm();
        $form = "";
        if ($paymentForm) {
            $paymentForm = json_decode($paymentForm);
            if (is_object($paymentForm) && is_object($paymentForm->data)) {
                $form = $paymentForm->data->form;
            }
        }
        $this->context->smarty->assign($this->getButtonTemplateVars($cart));
        $this->context->smarty->assign(array(
            'form'          => $form,
            'spinner'       => $spinner,
            'iframe'        => $iframe,
            'css'           => $css,
            'prestashopCss' => $prestashopCss,
            'checkoutUrl'   => $cancelUrl,
        ));
        if (_PS_VERSION_ < 1.7) {
            $this->setTemplate('payment-15.tpl');
        } else {
            $this->setTemplate('module:paylater/views/templates/front/payment-17.tpl');
        }
    }
}
