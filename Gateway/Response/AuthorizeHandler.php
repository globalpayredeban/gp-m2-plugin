<?php

namespace Globalpay\PaymentGateway\Gateway\Response;

use Exception;
use InvalidArgumentException;
use Magento\Framework\Validator\Exception as MagentoValidatorException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Globalpay\PaymentGateway\Gateway\Config\GatewayConfig;
use Globalpay\PaymentGateway\Helper\Logger;
use Globalpay\PaymentGateway\Helper\UtilManagement;


class AuthorizeHandler implements HandlerInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * AuthorizeHandler constructor.
     * @param GatewayConfig $config
     */
    public function __construct(GatewayConfig $config)
    {
        $this->logger = $config->logger;
    }

    /**
     * @inheritDoc
     * @throws MagentoValidatorException
     * @throws Exception
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment']) || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        if (!isset($response['transaction']['status'])) {
            $this->logger->error(sprintf('AuthorizeHandler.handle $msg: response does not have status field'));
            throw new MagentoValidatorException(__('Sorry, your payment could not be processed. (Code: ERR01)'));
        }

        $transaction = $response['transaction'];
        $card = $response['card'];

        $status = $transaction['status'];
        $transaction_id = $transaction['id'];
        $authorization_code = isset($transaction['authorization_code']) ? $transaction['authorization_code'] : null;
        $status_detail = $transaction['status_detail'];
        $message = $transaction['message'];
        $carrier_code = $transaction['carrier_code'];
        $card_tr = $card['transaction_reference'];
        $card_bin = $card['bin'];
        $card_termination = $card['number'];
        $card_type = $card['type'];

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $handlingSubject['payment'];
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        $is_direct_capture = $payment->getAdditionalInformation('is_direct_capture');
        if (($is_direct_capture && $status != 'success') || (!$is_direct_capture && $status == 'failure')) {
            $rejected_msg = __('Sorry, your payment could not be processed. (Code: %1)', $status_detail);
            throw new MagentoValidatorException($rejected_msg);
        }

        $payment->setTransactionId($transaction_id);
        $payment->setIsTransactionClosed(0);
        $payment->setAdditionalInformation('authorization_code', $authorization_code);
        $payment->setAdditionalInformation('status_detail', $status_detail);
        $payment->setAdditionalInformation('message', $message);
        $payment->setAdditionalInformation('carrier_code', $carrier_code);
        $payment->setAdditionalInformation('card_tr', $card_tr);
        $payment->setAdditionalInformation('card_bin', $card_bin);
        $payment->setAdditionalInformation('card_termination', $card_termination);
        $payment->setAdditionalInformation('card_type', $card_type);

        // TODO: Review by Kount is status_detail 1
        if ($status_detail == 1) {
            UtilManagement::setStatusForReviewByKount($paymentDO, $this->logger, true);
        }
    }
}
