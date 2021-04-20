<?php

namespace Globalpay\PaymentGateway\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Config\Config;
use Globalpay\PaymentGateway\Helper\FooLogger;
use Globalpay\PaymentGateway\Helper\Logger;
use Globalpay\PaymentGateway\Model\Adminhtml\Source\Env;

class GatewayConfig extends Config
{
    // === CONSTANTS === //
    const GATEWAY_CODE = 'globalpay_gateway';
    const CODE = 'globalpay_gateway';
    const ACTIVE = 'active';
    const TITLE = 'title';
    const ENVIRONMENT = 'environment';
    const SUPPORTED_COUNTRIES = 'supported_countries';
    const SUPPORTED_CURRENCIES = 'supported_currencies';
    const DEBUG = 'debug';

    // CREDENTIALS OPTIONS
    const STG_ENV = 'staging';
    const PROD_ENV = 'production';
    const SERVER_TYPE = 'server';
    const CLIENT_TYPE = 'client';
    const CODE_PATH = '%s_%s_code';
    const KEY_PATH = '%s_%s_key';


    /**
     * @var Logger
     */
    public $logger;

    /**
     * @var string
     */
    private $method_code;

    /**
     * GatewayConfig constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(ScopeConfigInterface $scopeConfig, Logger $logger, $methodCode = self::CODE, $pathPattern = parent::DEFAULT_PATH_PATTERN)
    {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->method_code = $methodCode;

        // If debug is active, use logs
        $this->logger = $this->isDebug() ? $logger : new FooLogger();
    }

    /**
     * @return string
     */
    public function getMethodCode()
    {
        return $this->method_code;
    }

    // ============ Gateway Functions ============ //

    /**
     * @return bool
     */
    public function isGatewayActive()
    {
        $this->setMethodCode(self::GATEWAY_CODE);
        $is_active = (boolean)(int)$this->getValue(self::ACTIVE);
        $this->setMethodCode($this->getMethodCode());

        $this->logger->debug(sprintf('GatewayConfig.isGatewayActive %s', $is_active));
        return $is_active;
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        $this->setMethodCode(self::GATEWAY_CODE);
        $is_debug = (boolean)(int)$this->getValue(self::DEBUG);
        $this->setMethodCode($this->getMethodCode());
        return $is_debug;
    }

    /**
     * @return array
     */
    public function getSupportedCountries()
    {
        $this->setMethodCode(self::GATEWAY_CODE);
        $countries = explode(',', $this->getValue(self::SUPPORTED_COUNTRIES));
        $this->setMethodCode($this->getMethodCode());

        $this->logger->debug(sprintf('GatewayConfig.getSupportedCountries'), $countries);
        return $countries;
    }

    /**
     * @return array
     */
    public function getSupportedCurrencies()
    {
        $this->setMethodCode(self::GATEWAY_CODE);
        $currencies = explode(',', $this->getValue(self::SUPPORTED_CURRENCIES));
        $this->setMethodCode($this->getMethodCode());

        $this->logger->debug(sprintf('GatewayConfig.getSupportedCurrencies'), $currencies);
        return $currencies;
    }

    /**
     * @param $env
     * @param $type
     * @return array
     */
    private function getCredentials($env, $type)
    {
        $this->logger->debug(sprintf('GatewayConfig.getCredentials env: %s - type: %s', $env, $type));

        $this->setMethodCode(self::GATEWAY_CODE);
        $path_pattern = $env == self::PROD_ENV ? 'payment/%s/credentials/production/%s' : 'payment/%s/credentials/staging/%s';
        $this->setPathPattern($path_pattern);

        $code = sprintf(self::CODE_PATH, $env, $type);
        $key = sprintf(self::KEY_PATH, $env, $type);
        $credentials = [
            'application_code' => $this->getValue($code),
            'application_key' => $this->getValue($key)
        ];

        $this->setMethodCode($this->getMethodCode());
        $this->setPathPattern(self::DEFAULT_PATH_PATTERN);

        $this->logger->debug(sprintf('GatewayConfig.getCredentials'), $credentials);
        return $credentials;
    }

    /**
     * @return array
     */
    private function getStgServerCredentials()
    {
        return $this->getCredentials(self::STG_ENV, self::SERVER_TYPE);
    }

    /**
     * @return array
     */
    private function getStgClientCredentials()
    {
        return $this->getCredentials(self::STG_ENV, self::CLIENT_TYPE);
    }

    /**
     * @return array
     */
    private function getProdServerCredentials()
    {
        return $this->getCredentials(self::PROD_ENV, self::SERVER_TYPE);
    }

    /**
     * @return array
     */
    private function getProdClientCredentials()
    {
        return $this->getCredentials(self::PROD_ENV, self::CLIENT_TYPE);
    }

    /**
     * @return array
     */
    public function getServerCredentials()
    {
        return $this->isProduction() ? $this->getProdServerCredentials() : $this->getStgServerCredentials();
    }

    /**
     * @return array
     */
    public function getClientCredentials()
    {
        return $this->isProduction() ? $this->getProdClientCredentials() : $this->getStgClientCredentials();
    }

    // ============ Payment Method Functions ============ //

    /**
     * @return bool
     */
    public function isActive()
    {
        $is_gateway_active = $this->isGatewayActive();
        $is_payment_method_active = (boolean)(int)$this->getValue(self::ACTIVE);
        $is_active = $is_gateway_active && $is_payment_method_active;

        $this->logger->debug(sprintf('GatewayConfig.isActive: %s', $is_active));
        return $is_active;
    }

    /**
     * @return bool
     */
    public function isProduction()
    {
        $is_production = (boolean)($this->getValue(self::ENVIRONMENT) == Env::PRODUCTION);

        $this->logger->debug(sprintf('GatewayConfig.isProduction: %s', $is_production));
        return $is_production;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        $title = $this->getValue(self::TITLE);

        $this->logger->debug(sprintf('GatewayConfig.getTitle: %s', $title));
        return $title;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        $env = $this->getValue(self::ENVIRONMENT);

        $this->logger->debug(sprintf('GatewayConfig.getEnvironment: %s', $env));
        return $env;
    }

}
