<?php
namespace Globalpay\PaymentGateway\Controller\PlaceOrder;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Api\AgreementsValidatorInterface;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Paypal\Model\Api\ProcessableException;
use Magento\Quote\Model\QuoteManagement;


use Globalpay\PaymentGateway\Controller\AbstractController;
use Globalpay\PaymentGateway\Gateway\Config\LinkToPayConfig;
use Globalpay\PaymentGateway\Helper\Logger;

/**
 * Class PlaceOrder
 */
class PlaceOrder extends AbstractController
{
    const LTP_PATH = "globalpay.com/linktopay/init_order/";
    /**
     * @var AgreementsValidatorInterface
     */
    protected $agreementsValidator;

    /**
    * @var LinkToPayConfig
    */
    protected $config;

    /**
    * @var StoreManagerInterface
    */
    protected $storeManager;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param QuoteManagement $quoteManagement
     * @param AgreementsValidatorInterface $agreementValidator
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param LinkToPayConfig $config
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        QuoteManagement $quoteManagement,
        AgreementsValidatorInterface $agreementValidator,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        LinkToPayConfig $config,
        Logger $logger
    ) {
        $this->agreementsValidator = $agreementValidator;
        $this->storeManager        = $storeManager;
        $this->logger              = $logger;
        $this->config              = new LinkToPayConfig($scopeConfig, $this->logger);
        parent::__construct(
            $context,
            $checkoutSession,
            $quoteManagement,
            $logger
        );
    }

    /**
     * Submit the order
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        if ($this->isValidationRequired() &&
            !$this->agreementsValidator->isValid(array_keys($this->getRequest()->getPost('agreement', [])))
        ) {
            $e = new \Magento\Framework\Exception\LocalizedException(
                __('Please agree to all the terms and conditions before placing the order.')
            );
            $this->messageManager->addExceptionMessage(
                $e,
                $e->getMessage()
            );
            $this->_redirect('checkout/cart');
            return;
        }

        try {
            $quoteId = $this->_getQuote()->getId();
            $this->_getCheckoutSession()->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);
            $this->_initOrder();
            $order = $this->_getCheckoutSession()->getLastRealOrder();
            if ($order) {
                $this->_getCheckoutSession()->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId())
                    ->setLastOrderStatus($order->getStatus());
            } else {
                $this->messageManager->addErrorMessage(
                    __('Order could not be generated.')
                );
                $this->_redirect("checkout/cart");
            }
            $url = $this->_getLinkToPayURL($order);
            $this->logger->debug(print_r($url, true));
            if ($url) {
                $order->getPayment()->setAdditionalInformation('expiration_days', $this->config->expirationDays());
                $order->getPayment()->setAdditionalInformation('ltp_url', $url);
                $order->save();
                $this->getResponse()->setRedirect($url);
                return;
            } else {
                $order->setStatus($order::STATE_CANCELED);
                $order->save();
                $this->messageManager->addErrorMessage(
                    __('LinkToPay could not be generated for this order.')
                );
                $this->_redirect("sales/order/view/order_id/{$order->getId()}");
            }
        } catch (ProcessableException $e) {
            $this->messageManager->addErrorMessage($e);
            $this->_redirect('checkout/cart');
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t place the last order.')
            );
            $this->_redirect('sales/order/history');
        }
    }

    /**
     * Return true if agreements validation required
     *
     * @return bool
     */
    protected function isValidationRequired()
    {
        return is_array($this->getRequest()->getBeforeForwardInfo())
        && empty($this->getRequest()->getBeforeForwardInfo());
    }

    /**
     * Get LinkToPay URL to redirect.
     *
     * @param Magento\Sales\Model\Order $order
     * @return string|null
     */
    protected function _getLinkToPayURL($order)
    {
        $requestBody = $this->_getLinkToPayRequestBody($order);
        $url         = $this->_requestToLinkToPay($requestBody);
        return $url;
    }

    /**
     * Build body to request gateway services.
     *
     * @param Magento\Sales\Model\Order $order
     * @return array
     */
    protected function _getLinkToPayRequestBody($order)
    {
        $orderId        = $order->getIncrementId();
        $email          = $order->getCustomerEmail();
        $shippingMethod = $order->getShippingMethod();
        $description    = substr(sprintf('Payment of order #%s, Customer email: %s Shipping method: %s', $orderId, $email, $shippingMethod), 0, 247);
        $urlBase        = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $viewOrderURL   = $urlBase.'sales/order/view/order_id/'.$order->getId();
        return [
            "user" => [
               "id"        => $order->getCustomerId(),
               "email"     => $email,
               "name"      => $order->getCustomerFirstname(),
               "last_name" => $order->getCustomerLastname()
           ],
           "order" => [
               "dev_reference"     => $orderId,
               "description"       => $description,
               "amount"            => (float)$order->getGrandTotal(),
               "installments_type" => $this->config->allowInstallments(),
               "currency"          => $order->getOrderCurrencyCode()
           ],
           "configuration" => [
               "partial_payment"         => $this->config->allowPartialPayments(),
               "expiration_days"         => $this->config->expirationDays(),
               "allowed_payment_methods" => ["All", "Cash", "BankTransfer", "Card"],
               "success_url"             => $viewOrderURL,
               "failure_url"             => $viewOrderURL,
               "pending_url"             => $viewOrderURL,
               "review_url"              => $viewOrderURL
           ]
        ];
    }

    /**
     * Request to LinkToPay services to get the URL to redirect.
     *
     * @param array $requestBody
     * @return string|null
     */
    protected function _requestToLinkToPay($requestBody)
    {
        $urlRequest        = ($this->config->getEnvironment() == 'stg') ? 'https://noccapi-stg.'.self::LTP_PATH : 'https://noccapi.'.self::LTP_PATH;
        $serverCredentials = $this->config->getServerCredentials();

        $payload   = json_encode($requestBody);
        $authToken = $this->_generateauthToken($serverCredentials);
        $ch        = curl_init($urlRequest);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Auth-Token:' . $authToken));
        try {
          $response    = curl_exec($ch);
          $getResponse = json_decode($response, true);
          $paymentURL  = $getResponse['data']['payment']['payment_url'];
        } catch (Exception $e) {
          $paymentURL = null;
        }
        curl_close($ch);
        return $paymentURL;
    }

    /**
     * Generate gateway auth token to request.
     *
     * @param array $serverCredentials
     * @return string
     */
    protected function _generateAuthToken($serverCredentials)
    {
        $timestamp   = (string)(time());
        $tokenString = $serverCredentials['application_key'] . $timestamp;
        $tokenHash   = hash('sha256', $tokenString);
        $authToken   = base64_encode($serverCredentials['application_code'] . ';' . $timestamp . ';' . $tokenHash);
        return $authToken;
    }
}
