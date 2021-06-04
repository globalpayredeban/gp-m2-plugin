<?php

namespace Globalpay\PaymentGateway\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class InstallmentsTypes implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value'=> -1, 'label'=> __('this will apply for future integrations')],
        ];
    }
}
