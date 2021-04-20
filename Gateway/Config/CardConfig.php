<?php

namespace Globalpay\PaymentGateway\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Globalpay\PaymentGateway\Helper\Logger;

class CardConfig extends GatewayConfig
{
    # CONSTANTS
    const CODE = 'globalpay_card';
    const SUPPORTED_BRANDS = 'supported_brands';
    const ALLOW_INSTALLMENTS = 'allow_installments';

    /**
     * CardConfig constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(ScopeConfigInterface $scopeConfig, Logger $logger, $methodCode = self::CODE, $pathPattern = parent::DEFAULT_PATH_PATTERN)
    {
        parent::__construct($scopeConfig, $logger, $methodCode, $pathPattern);
    }

    /**
     * @return array
     */
    public function getSupportedBrands()
    {
        $supported_brands = explode(',', $this->getValue(self::SUPPORTED_BRANDS));
        $this->logger->debug(sprintf('CardConfig.getSupportedBrands'), $supported_brands);
        return $supported_brands;
    }

    /**
     * @return bool
     */
    public function allowInstallments()
    {
        $allows_installments = (boolean)(int)$this->getValue(self::ALLOW_INSTALLMENTS);
        $this->logger->debug(sprintf('CardConfig.allowInstallments: %s', $allows_installments));
        return $allows_installments;
    }

}
