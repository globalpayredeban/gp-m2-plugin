<?php

namespace Globalpay\PaymentGateway\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Country implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'MX', 'label' => __('Mexico')],
            ['value' => 'EC', 'label' => __('Ecuador')],
            ['value' => 'CO', 'label' => __('Colombia')],
            ['value' => 'BR', 'label' => __('Brazil')],
            ['value' => 'PE', 'label' => __('Peru')],
            ['value' => 'AR', 'label' => __('Argentina')],
            ['value' => 'VE', 'label' => __('Venezuela')],
            ['value' => 'CL', 'label' => __('Chile')],
        ];
    }
}
