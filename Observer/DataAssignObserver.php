<?php

namespace Globalpay\PaymentGateway\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\Exception as MagentoValidatorException;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Globalpay\PaymentGateway\Gateway\Config\CardConfig;
use Globalpay\PaymentGateway\Gateway\Config\LinkToPayConfig;
use Globalpay\PaymentGateway\Gateway\Config\GatewayConfig;
use Globalpay\PaymentGateway\Helper\Logger;

class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * @var Logger
     */
    public $logger;

    /**
     * DataAssignObserver constructor.
     * @param GatewayConfig $config
     */
    public function __construct(GatewayConfig $config)
    {
        $this->logger = $config->logger;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $method = $this->readMethodArgument($observer);
        $data = $this->readDataArgument($observer);
        $paymentInfo = $method->getInfoInstance();
        $additional_data = $data->getDataByKey('additional_data');
        switch ($data->getDataByKey('method')) {

            case CardConfig::CODE:
                $installment = isset($additional_data['installment']) ? $additional_data['installment'] : 1;
                $token = isset($additional_data['token']) ? $additional_data['token'] : null;

                $paymentInfo->setAdditionalInformation('installment', $installment);
                $paymentInfo->setAdditionalInformation('token', $token);
                break;

            //  Add here more payment methods as: LTP, Cash, PSE
            case LinkToPayConfig::CODE:
                $installment = isset($additional_data['installment']) ? $additional_data['installment'] : 1;

                $paymentInfo->setAdditionalInformation('installment', $installment);
                break;
        }
        $this->logger->debug(sprintf('DataAssignObserver.execute $paymentInfo:'), (array)$paymentInfo);

    }
}
