<?php

namespace Globalpay\PaymentGateway\Gateway\Request;

use InvalidArgumentException;
use Magento\Framework\Validator\Exception as MagentoValidatorException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Payment;
use Globalpay\PaymentGateway\Gateway\Config\CardConfig;
use Globalpay\PaymentGateway\Helper\Logger;


class TransactionRequest implements BuilderInterface
{
    /**
     * @var Logger
     */
    public $logger;

    /**
     * CardPaymentAuthorizeRequest constructor.
     * @param CardConfig $config
     */
    public function __construct(CardConfig $config)
    {
        $this->logger = $config->logger;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     * @throws MagentoValidatorException
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment']) || !$buildSubject['payment'] instanceof PaymentDataObjectInterface) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $buildSubject['payment'];
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();
        $address = $order->getShippingAddress();
        $order_id = $order->getOrderIncrementId();
        $email = $address->getEmail();
        $shipping_method = $payment->getOrder()->getShippingMethod();

        if ($payment->getDataByKey('method') !== CardConfig::CODE) {
            throw new MagentoValidatorException(__("Invalid Payment Method"));
        }
        $description = substr(sprintf('Payment of order #%s, Customer email: %s Shipping method: %s', $order_id, $email, $shipping_method), 0, 247);

        $transaction_body = [
            'user' => [
                'id' => $order->getCustomerId() ? (string)$order->getCustomerId() : $email,
                'email' => $email,
                'phone' => $address->getTelephone(),
                'ip_address' => $order->getRemoteIp(),
            ],
            'order' => [
                'amount' => (float)$order->getGrandTotalAmount(),
                'description' => $description,
                'dev_reference' => (string)$order_id,
                'installments' => (int)$payment->getAdditionalInformation('installment'),
                'vat' => (float)0
            ],
            'card' => [
                'token' => $payment->getAdditionalInformation('token')
            ],
            'extra_data' => [
                'additional_amount' => isset($buildSubject['amount']) ? $buildSubject['amount'] : null,  // User only for refund, validate if apply for capture and authorize
            ],
            'objects' => [
                'payment' => $payment,
                'order' => $order,
            ]
        ];

        return $transaction_body;
    }
}
