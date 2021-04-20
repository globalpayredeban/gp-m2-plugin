<?php

namespace Globalpay\PaymentGateway\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Globalpay\PaymentGateway\Gateway\Config\GatewayConfig;
use Globalpay\PaymentGateway\Helper\Logger;

class CurrencyValidator extends AbstractValidator
{
    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var GatewayConfig
     */
    private $config;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param GatewayConfig $config
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        GatewayConfig $config
    )
    {
        parent::__construct($resultFactory);
        $this->config = $config;
        $this->logger = $config->logger;
    }

    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $currency = $validationSubject['currency'];
        $supported_currencies = $this->config->getSupportedCurrencies();
        $isValid = in_array($currency, $supported_currencies);

        $this->logger->info(sprintf('CurrencyValidator.validate %s is valid: %s', $currency, $isValid));
        return $this->createResult($isValid);
    }
}
