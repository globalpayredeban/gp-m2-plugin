<?php

namespace Globalpay\PaymentGateway\Block;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
use Globalpay\PaymentGateway\Model\Adminhtml\Source\Brand;

class Info extends ConfigurableInfo
{
    /**
     * Returns label
     *
     * @param string $field
     * @return Phrase
     */
    protected function getLabel($field)
    {
        return __($field);
    }

    /**
     * Prepare information to show
     *
     * @param DataObject|array|null $transport
     * @return DataObject
     * @throws LocalizedException
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $payment = $this->getInfo();
        $method = $this->getMethod()->getCode();
        if ($method == 'globalpay_card') {
            $info = [
                'Card' => sprintf('%s XXXX %s', $payment->getAdditionalInformation('card_bin'), $payment->getAdditionalInformation('card_termination')),
                'Card Type' => Brand::getBrandName($payment->getAdditionalInformation('card_type')),
                'Authorization Code' => $payment->getAdditionalInformation('authorization_code'),
                'Installments' => $payment->getAdditionalInformation('installment'),
                'Installments Type' => $payment->getAdditionalInformation('installment_type'),
                'Message' => $payment->getAdditionalInformation('message'),
                'Carrier Code' => $payment->getAdditionalInformation('carrier_code'),
            ];

            if (!$this->getIsSecureMode()) {
                $info['Carrier Code'] = $payment->getAdditionalInformation('carrier_code');
                $info['Message'] = $payment->getAdditionalInformation('message');
                $info['Status Detail'] = $payment->getAdditionalInformation('status_detail');
                $info['Add Card Transaction'] = $payment->getAdditionalInformation('card_tr');
            }
        } elseif ($method == 'globalpay_ltp') {
            $info = [
                'LinkToPay'          => $payment->getAdditionalInformation('ltp_url'),
                'Expiration Days'    => $payment->getAdditionalInformation('expiration_days'),
                'Authorization Code' => $payment->getAdditionalInformation('authorization_code'),
                'Message'            => $payment->getAdditionalInformation('message'),
                'Carrier Code'       => $payment->getAdditionalInformation('carrier_code'),
            ];
        }

        return $transport->addData($info);
    }

}
