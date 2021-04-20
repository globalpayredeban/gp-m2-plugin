<?php

namespace Globalpay\PaymentGateway\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Currency implements OptionSourceInterface
{
    const ALLOW_AUTHORIZE = ['MXN', 'BRL', 'PEN'];

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'MXN', 'label' => __('Mexican Peso')],
            ['value' => 'USD', 'label' => __('US Dollar')],
            ['value' => 'COP', 'label' => __('Colombian Peso')],
            ['value' => 'BRL', 'label' => __('Brazilian Real')],
            ['value' => 'PEN', 'label' => __('Peruvian Nuevo Sol')],
            ['value' => 'ARS', 'label' => __('Argentine Peso')],
            ['value' => 'VEF', 'label' => __('Venezuelan Bolívar')],
            ['value' => 'CLP', 'label' => __('Chilean Peso')],
        ];
    }

    public static function validateForAuthorize($currency)
    {
        return in_array($currency, self::ALLOW_AUTHORIZE);
    }
}
