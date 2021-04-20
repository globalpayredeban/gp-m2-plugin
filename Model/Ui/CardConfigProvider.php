<?php

namespace Globalpay\PaymentGateway\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Globalpay\PaymentGateway\Gateway\Config\CardConfig;

/**
 * Class ConfigProvider
 */
final class CardConfigProvider implements ConfigProviderInterface
{
    const CODE = CardConfig::CODE;

    /**
     * @var CardConfig
     */
    private $config;

    /**
     * Constructor
     *
     * @param CardConfig $config
     */
    public function __construct(CardConfig $config)
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
                    'is_active' => $this->config->isActive(),
                    'title' => $this->config->getTitle(),
                    'credentials' => $this->config->getClientCredentials(),
                    'environment' => $this->config->getEnvironment(),
                    'brands' => $this->config->getSupportedBrands(),
                    'allow_installments' => $this->config->allowInstallments(),
                ]
            ]
        ];
        $this->config->logger->debug(sprintf('CardConfigProvider.getConfig'), $config);
        return $config;
    }
}
