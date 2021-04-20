<?php

namespace Globalpay\PaymentGateway\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Model\Order\Payment;

class UtilManagement
{
    /**
     * @param PaymentDataObjectInterface $paymentDO
     * @param $logger
     * @param bool $authorize_origin
     * @throws LocalizedException
     */
    public static function setStatusForReviewByKount(PaymentDataObjectInterface $paymentDO, $logger, bool $authorize_origin = null)
    {
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $order = $payment->getOrder();

        if ($authorize_origin) {
            $logger->debug('UtilManagement.setStatusForReviewByKount Setting Pending');
            $payment->setIsTransactionPending(1);
            $payment->setAdditionalInformation('update_in_handler', (boolean)1);
            $logger->debug(sprintf('UtilManagement.setStatusForReviewByKount orderState: %s - orderStatus: %s', $order->getState(), $order->getStatus()));

        } elseif ($payment->getAdditionalInformation('update_in_handler')) {
            $logger->debug('UtilManagement.setStatusForReviewByKount Remove pending');
            $payment->setIsTransactionPending(0);
            $order->setState($order::STATE_PROCESSING)->setStatus($order::STATE_PAYMENT_REVIEW)->save();
            $payment->setAdditionalInformation('update_in_handler', (boolean)0);
            $payment->save();
            $logger->debug(sprintf('UtilManagement.setStatusForReviewByKount orderState: %s - orderStatus: %s', $order->getState(), $order->getStatus()));
            echo('<script>window.location.reload()</script>');
        }
    }
}
