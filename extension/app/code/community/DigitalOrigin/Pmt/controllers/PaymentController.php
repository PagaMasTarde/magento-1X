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
        /** @var Mage_Sales_Model_Order $order */
        $order = $salesOrder->loadByIncrementId($orderId);
        /** @var Mage_Core_Helper_Data $mageCore */
        $mageCore = Mage::helper('core');


        if (is_object($order) && $order->getState() != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
            $this->_redirect('checkout/cart');
        }



            $orderData = json_decode($mageCore->jsonEncode($order->getData()), true);
        $customerData = json_decode($mageCore->jsonEncode($customer->getData()), true);
        $itemsData = json_decode($mageCore->jsonEncode($order->getItemsCollection()->getData()), true);
        $addressData = json_decode($mageCore->jsonEncode($order->getAddressesCollection()->getData()), true);
        $moduleConfig = Mage::getStoreConfig('payment/paylater');

        echo '<pre>';
        echo (
            json_encode([
                'order' => $orderData,
                'customer' => $customerData,
                'items' => $itemsData,
                'address' => $addressData,
                'module' => $moduleConfig,
                'url' => [
                    'ok' => Mage::getUrl('pmt/notify'),
                    'ko' => Mage::getUrl('checkout/cart'),
                    'callback' => Mage::getUrl('pmt/notify'),
                    'cancelled' => Mage::getUrl('checkout/cart'),
                ],
                'metadata' => [
                    'magento' => Mage::getVersion(),
                    'pmt' => (string) Mage::getConfig()->getNode()->modules->DigitalOrigin_Pmt->version,
                    'php' => phpversion(),
                ]

            ])
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
