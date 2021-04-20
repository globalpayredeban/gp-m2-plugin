<?php

namespace Globalpay\PaymentGateway\Gateway\Http\Client;

use Magento\Sales\Model\Order\Payment;
use Globalpay\Exceptions\GlobalpayErrorException;
use Globalpay\Globalpay;
use Globalpay\PaymentGateway\Gateway\Config\CardConfig;
use Globalpay\PaymentGateway\Gateway\Config\GatewayConfig;
use Globalpay\PaymentGateway\Model\Adminhtml\Source\Currency;

class CaptureClient extends AbstractClient
{
    /**
     * CaptureClient constructor.
     * @param Globalpay $adapter
     * @param GatewayConfig $gateway_config
     * @param CardConfig $config
     */
    public function __construct(Globalpay $adapter, GatewayConfig $gateway_config, CardConfig $config)
    {
        parent::__construct($adapter, $gateway_config);
        $this->config = $config;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    protected function process(array $request_body)
    {
        $is_production = $this->config->isProduction();
        $credentials = $this->config->getServerCredentials();

        $this->adapter->init($credentials['application_code'], $credentials['application_key'], $is_production);

        $charge = $this->adapter::charge();

        /** @var Payment $payment */
        $payment = $request_body['objects']['payment'];
        $order_obj = $request_body['objects']['order'];

        $response = [];

        if (is_null($payment->getParentTransactionId())) {
            $this->logger->debug('CaptureClient.process Authorization is required...');
            $payment->setAdditionalInformation('is_direct_capture', 1);
            $payment->authorize(1, $request_body['order']['amount']);
        }

        if ($payment->getAdditionalInformation('status_detail') == '1') {
            $user = [
                'id' => $request_body['user']['id']
            ];
            $this->logger->debug('CaptureClient.process Use verify for review transactions...');

            try {
                $response = (array)$charge->verify('BY_AMOUNT', (string)$request_body['order']['amount'], $payment->getParentTransactionId(), $user, true);
                $response = json_decode(json_encode($response), true);
                $status_detail = $response['transaction']['status_detail'];
                if ($status_detail !== 0) {
                    return $response;
                }
            } catch (GlobalpayErrorException $e) {
                $code = $e->getCode();
                if ($code !== 403) {
                    throw $e;
                }
            }

        }
        $transaction_id = !is_null($payment->getParentTransactionId()) ? $payment->getParentTransactionId() : $payment->getTransactionId();

        if (Currency::validateForAuthorize($order_obj->getCurrencyCode())) {
            $this->logger->debug('CaptureClient.process Consuming Capture...');
            $amount = isset($extra_data['additional_amount']) ? $extra_data['additional_amount'] : $request_body['order']['amount'];
            try {
                $response = $charge->capture($transaction_id, $amount, true);
            } catch (GlobalpayErrorException $e) {
                $message = $e->getMessage();
                if (strpos($message, "Transaction already captured") !== false) {
                    $response = [
                        'transaction' => [
                            'id' => $transaction_id,
                            'status' => 'success',
                            'status_detail' => 3,
                            'authorization_code' => $payment->getAdditionalInformation('authorization_code'),
                            'message' => $payment->getAdditionalInformation('message'),
                            'carrier_code' => $payment->getAdditionalInformation('carrier_code'),
                        ],
                    ];
                } else {
                    throw $e;
                }
            }

        } else {
            $this->logger->debug('CaptureClient.process Use mock for debited transactions...');
            $response = [
                'transaction' => [
                    'id' => $transaction_id,
                    'status' => 'success',
                    'status_detail' => $payment->getAdditionalInformation('status_detail'),
                    'authorization_code' => $payment->getAdditionalInformation('authorization_code'),
                    'message' => $payment->getAdditionalInformation('message'),
                    'carrier_code' => $payment->getAdditionalInformation('carrier_code'),
                ],
            ];
        }

        return (array)$response;
    }
}
