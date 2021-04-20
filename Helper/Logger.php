<?php

namespace Globalpay\PaymentGateway\Helper;

use Psr\Log\LoggerInterface;

class Logger
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Logger constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function critical($message, array $context = array())
    {
        $this->logger->critical($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function error($message, array $context = array())
    {
        $this->logger->error($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function warning($message, array $context = array())
    {
        $this->logger->warning($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function info($message, array $context = array())
    {
        $this->logger->info($message, $context);
    }

    /**
     * @inheritDoc
     */
    public function debug($message, array $context = array())
    {
        $this->logger->debug($message, $context);
    }
}
