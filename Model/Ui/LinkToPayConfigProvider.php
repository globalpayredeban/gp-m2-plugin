<?php

namespace Globalpay\PaymentGateway\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Globalpay\PaymentGateway\Gateway\Config\LinkToPayConfig;

/**
 * Class LinkToPayConfigProvider
 */
final class LinkToPayConfigProvider implements ConfigProviderInterface
{
    const CODE = LinkToPayConfig::CODE;

    /**
     * @var LinkToPayConfig
     */
    private $config;

    /**
     * Constructor
     *
     * @param LinkToPayConfig $config
     */
    public function __construct(LinkToPayConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [
            'payment' => [
                self::CODE => [
                    'is_active'              => $this->config->isActive(),
                    'title'                  => $this->config->getTitle(),
                    'credentials'            => $this->config->getClientCredentials(),
                    'environment'            => $this->config->getEnvironment(),
                    'allow_installments'     => $this->config->allowInstallments(),
                    'allow_partial_payments' => $this->config->allowPartialPayments(),
                    'expiration_days'        => $this->config->expirationDays(),
                ]
            ]
        ];
        $this->config->logger->debug(sprintf('LinkToPayConfigProvider.getConfig'), $config);
        return $config;
    }
}
