<?php

namespace Globalpay\PaymentGateway\Gateway\Http\Client;

use Magento\Framework\Validator\Exception as MagentoValidatorException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Payment\Exceptions\PaymentErrorException as GlobalpayErrorException;
use Payment\Payment as Globalpay;
use Globalpay\PaymentGateway\Gateway\Config\GatewayConfig;
use Globalpay\PaymentGateway\Helper\Logger;

abstract class AbstractClient implements ClientInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var GatewayConfig $config
     */
    protected $config;

    /**
     * @var Globalpay
     */
    protected $adapter;

    /**
     * AbstractClient constructor.
     * @param Globalpay $adapter
     * @param GatewayConfig $config
     */
    public function __construct(Globalpay $adapter, GatewayConfig $config)
    {
        $this->logger = $config->logger;
        $this->adapter = $adapter;
    }

    /**
     * @param TransferInterface $transferObject
     * @return array
     * @throws MagentoValidatorException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $data = $transferObject->getBody();
        $this->logger->debug(sprintf('AbstractClient.placeRequest client: %s', static::class));
        $this->logger->debug('AbstractClient.placeRequest request: ', array($data));

        $response = [];
        try {
            $response = $this->process($data);
            $response = json_decode(json_encode($response), true);
        } catch (GlobalpayErrorException $e) {
            $code = $e->getCode();
            $message = $e->getMessage();
            $this->logger->error(sprintf('AbstractClient.placeRequest error: %s, message: %s', $code, $message));

            $rejected_msg = __('Sorry, your payment could not be processed. (Code: %1)', $code);
            $this->logger->error(sprintf('AbstractClient.placeRequest $msg: %s', $rejected_msg));
            throw new MagentoValidatorException($rejected_msg);
        }

        $this->logger->debug(sprintf('AbstractClient.placeRequest client: %s', static::class));
        $this->logger->debug('AbstractClient.placeRequest response: ', array($response));
        return (array)$response;
    }

    /**
     * @param array $data
     * @return mixed
     */
    abstract protected function process(array $data);
}
