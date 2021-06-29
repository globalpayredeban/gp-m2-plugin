<?php
namespace Globalpay\PaymentGateway\Model;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Webapi\Exception;

use Globalpay\PaymentGateway\Api\WebhookInterface;
use Globalpay\PaymentGateway\Helper\Logger;
use Globalpay\PaymentGateway\Gateway\Config\GatewayConfig;


class OrderWebhook implements WebhookInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var OrderInterface
     */
    protected $order;

    /**
     * @var GatewayConfig
     */
    protected $config;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * OrderWebhook constructor.
     * @param Logger $logger
     * @param RequestInterface $request
     * @param OrderInterface $order
     * @param GatewayConfig $config
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Logger $logger,
        OrderInterface $order,
        GatewayConfig $config,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->order  = $order;
        $this->logger = $logger;
        $this->config = new GatewayConfig($scopeConfig, $this->logger);
    }

    /**
     * Method that manages the update order via webhook.
     * @return void
     * @throws Exception
     */
    public function updateOrderWebhook() {
        $params           = json_decode(file_get_contents('php://input'), true);
        $status           = $params["transaction"]['status'];
        $status_detail    = (int)$params["transaction"]['status_detail'];
        $transaction_id   = $params["transaction"]['id'];
        $dev_reference    = $params["transaction"]['dev_reference'];
        $pg_stoken        = $params["transaction"]['stoken'];
        $application_code = $params["transaction"]['application_code'];
        $auth_code        = $params["transaction"]['authorization_code'];
        $message          = $params["transaction"]['message'] ?? 'Not apply';
        $carrier_code     = $params["transaction"]['carrier_code'] ?? 'Not apply';
        $amount           = (float)$params["transaction"]['amount'];
        $user_id          = $params["user"]['id'];

        $this->validateStoken($user_id, $transaction_id, $application_code, $pg_stoken);

        $order = $this->order->loadByIncrementId($dev_reference);
        if (!$order->getId()) {
            throw new Exception(__('Order not found'), 0, Exception::HTTP_INTERNAL_ERROR);
        }

        if ($order->getStatus() == $order::STATE_COMPLETE) {
            throw new Exception(__('Order status is complete, can\'t change.'), 0, Exception::HTTP_BAD_REQUEST);
        }
        $payment = $order->getPayment();
        $payment->setAdditionalInformation('authorization_code', $auth_code);
        $payment->setAdditionalInformation('message', $message);
        $payment->setAdditionalInformation('carrier_code', $carrier_code);

        $pg_status_m2 = [
            0 => $order::STATE_PENDING_PAYMENT,
            3 => $order::STATE_PROCESSING,
            7 => 'refund',
            8 => $order::STATE_CANCELED,
        ];
        $status_code = $pg_status_m2[$status_detail];
        if ($status_code == $order::STATE_CANCELED) {
            $transaction_id_m2 = !is_null($payment->getParentTransactionId()) ? $payment->getParentTransactionId() : $payment->getTransactionId();
            $payment->setAmountCanceled($amount);
            $payment->setTransactionId($transaction_id_m2);
            $payment->setIsTransactionClosed(1);
            $payment->setShouldCloseParentTransaction(1);
        } elseif ($status_code == $order::STATE_PROCESSING)
        {
            $order->setTotalPaid($amount);
            $order->setBaseTotalPaid($amount);
        }
        $order->setStatus($status_code);
        $order->save();
        $payment->save();
    }

    /**
     * Method to validate the request stoken authenticy.
     * @param string $user_id
     * @param string $transaction_id
     * @param string $application_code
     * @param string $pg_stoken
     * @return void
     * @throws Exception
     */
    private function validateStoken($user_id, $transaction_id, $application_code, $pg_stoken) {
        $credentials_client = $this->config->getServerCredentials();
        $credentials_server = $this->config->getClientCredentials();
        $codes_keys         = [
            $credentials_client['application_code'] => $credentials_client['application_key'],
            $credentials_server['application_code'] => $credentials_server['application_key'],
        ];
        $app_key = $codes_keys[$application_code];
        $for_md5 = "{$transaction_id}_{$application_code}_{$user_id}_{$app_key}";
        $stoken  = md5($for_md5);
        if ($stoken != $pg_stoken) {
            throw new Exception(__('stokens did not match.'), 0, Exception::HTTP_UNAUTHORIZED);
        }
    }
}
