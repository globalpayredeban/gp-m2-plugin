<?php

namespace Globalpay\PaymentGateway\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Env implements OptionSourceInterface
{
    const STAGING = 'stg';
    const PRODUCTION = 'prod';

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::STAGING, 'label' => __('Staging')],
            ['value' => self::PRODUCTION, 'label' => __('Production')]
        ];
    }

}
