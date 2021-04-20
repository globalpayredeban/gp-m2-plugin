<?php

namespace Globalpay\PaymentGateway\Gateway\Response;

use InvalidArgumentException;
use Magento\Framework\Validator\Exception as MagentoValidatorException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Globalpay\PaymentGateway\Gateway\Config\GatewayConfig;
use Globalpay\PaymentGateway\Helper\Logger;


class CaptureHandler implements HandlerInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * CaptureHandler constructor.
     * @param GatewayConfig $config
     */
    public function __construct(GatewayConfig $config)
    {
        $this->logger = $config->logger;
    }

    /**
     * @inheritDoc
     * @throws MagentoValidatorException
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment']) || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        if (!isset($response['transaction']['status'])) {
            $this->logger->error(sprintf('CaptureHandler.handle $msg: response does not have status field'));
            throw new MagentoValidatorException(__('Sorry, your payment could not be processed. (Code: ERR01)'));
        }

        $transaction = $response['transaction'];

        $status = $transaction['status'];
        $authorization_code = isset($transaction['authorization_code']) ? $transaction['authorization_code'] : null;
        $status_detail = $transaction['status_detail'];
        $message = $transaction['message'];
        $carrier_code = $transaction['carrier_code'];

        if ($status !== 'success') {
            $rejected_msg = __('Sorry, your payment could not be processed. (Code: %1)', $status_detail);
            throw new MagentoValidatorException($rejected_msg);
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        $transaction_id = !is_null($payment->getParentTransactionId()) ? $payment->getParentTransactionId() : $payment->getTransactionId();
        $payment->setAdditionalInformation('authorization_code', $authorization_code);
        $payment->setAdditionalInformation('status_detail', $status_detail);
        $payment->setAdditionalInformation('message', $message);
        $payment->setAdditionalInformation('carrier_code', $carrier_code);
        $payment->setTransactionId($transaction_id);
        $payment->setIsTransactionClosed(1);
    }
}
