<?php

namespace Globalpay\PaymentGateway\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Globalpay\PaymentGateway\Helper\Logger;

class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @var Logger
     */
    public $logger;

    /**
     * TransferFactory constructor.
     * @param TransferBuilder $transferBuilder
     * @param Logger $logger
     */
    public function __construct(TransferBuilder $transferBuilder, Logger $logger)
    {
        $this->transferBuilder = $transferBuilder;
        $this->logger = $logger;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        {
            return $this->transferBuilder
                ->setBody($request)
                ->build();
        }
    }
}
