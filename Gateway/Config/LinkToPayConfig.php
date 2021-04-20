<?php

namespace Globalpay\PaymentGateway\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Globalpay\PaymentGateway\Helper\Logger;

class LinkToPayConfig extends GatewayConfig
{
    # CONSTANTS
    const CODE = 'globalpay_ltp';
    const ALLOW_INSTALLMENTS = 'allow_installments';
    const ALLOW_PARTIAL_PAYMENTS = 'allow_partial_payments';
    const EXPIRATION_DAYS = 'expiration_days';


    /**
     * LinkToPayConfig constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        $methodCode = self::CODE,
        $pathPattern = parent::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $logger, $methodCode, $pathPattern);
    }

    /**
     * @return bool
     */
    public function allowInstallments()
    {
        $allow_installments = (boolean)(int)$this->getValue(self::ALLOW_INSTALLMENTS);
        $this->logger->debug(sprintf('LinkToPayConfig.allowInstallments: %s', $allow_installments));
        return $allow_installments;
    }

    /**
     * @return bool
     */
    public function allowPartialPayments()
    {
        $allow_partial_payments = (boolean)(int)$this->getValue(self::ALLOW_PARTIAL_PAYMENTS);
        $this->logger->debug(sprintf('LinkToPayConfig.allowPartialPayments: %s', $allow_partial_payments));
        return $allow_partial_payments;
    }

    /**
     * @return integer
     */
    public function expirationDays()
    {
        $expiration_days = (int)$this->getValue(self::EXPIRATION_DAYS);
        $this->logger->debug(sprintf('LinkToPayConfig.expirationDays: %s', $expiration_days));
        return $expiration_days;
    }
}
