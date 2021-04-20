<?php

namespace Globalpay\PaymentGateway\Gateway\Config\Handler;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;
use Globalpay\PaymentGateway\Gateway\Config\GatewayConfig;
use Globalpay\PaymentGateway\Helper\Logger;
use Globalpay\PaymentGateway\Helper\UtilManagement;

class CanCaptureHandler implements ValueHandlerInterface
{
    /**
     * @var Logger
     */
    public $logger;

    /**
     * CanCaptureHandler constructor.
     * @param GatewayConfig $config
     */
    public function __construct(GatewayConfig $config)
    {
        $this->logger = $config->logger;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function handle(array $subject, $storeId = null)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $subject['payment'];
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        UtilManagement::setStatusForReviewByKount($paymentDO, $this->logger);

        $is_authorized = $payment->getAmountAuthorized() > 0;
        $is_captured = $payment->getAmountPaid() > 0;
        $is_refunded = $payment->getAmountRefunded() > 0;
        $is_canceled = $payment->getAmountCanceled() > 0;
        $is_closed = $payment->getData('is_transaction_close');

        $can_capture = $is_authorized && !$is_captured && !$is_closed && !$is_refunded && !$is_canceled;
        $this->logger->debug(sprintf('CanCaptureHandler.handle $can_capture %s .', $can_capture));
        return $can_capture;
    }
}
