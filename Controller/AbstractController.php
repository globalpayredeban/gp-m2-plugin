<?php

namespace Globalpay\PaymentGateway\Controller;

use Magento\Framework\App\Action\Action as AppAction;

use Globalpay\PaymentGateway\Helper\Logger;

/**
 * Abstract Controller
 */
abstract class AbstractController extends AppAction
{
    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote = false;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $_quoteManagement;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\QuoteManagement $quoteManagement
     * @param Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        Logger $logger
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_quoteManagement = $quoteManagement;
        $this->_logger          = $logger;
        parent::__construct($context);
    }

    /**
     * Instantiate order using quote
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initOrder()
    {
        $quote = $this->_getQuote();
        if (!$quote) {
            $this->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
            throw new \Magento\Framework\Exception\LocalizedException(__("We can not init order. {$quote->hasItems()}"));
        }
        // Create Order From Quote Object
        $this->_quoteManagement->submit($quote);
    }

    /**
     * Return checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Return checkout quote object
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }
}
