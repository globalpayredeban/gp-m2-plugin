<?php

namespace Globalpay\PaymentGateway\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Brand implements OptionSourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'VI', 'label' => 'Visa'],
            ['value' => 'MC', 'label' => 'Mastercard'],
            ['value' => 'AX', 'label' => 'American Express'],
            ['value' => 'DI', 'label' => 'Diners'],
            ['value' => 'DC', 'label' => 'Discover'],
            ['value' => 'EL', 'label' => 'Elo'],
            ['value' => 'CS', 'label' => 'Credisensa'],
            ['value' => 'SO', 'label' => 'Solidario'],
            ['value' => 'EX', 'label' => 'Exito'],
            ['value' => 'AK', 'label' => 'Alkosto'],
            ['value' => 'CD', 'label' => 'Codensa'],
            ['value' => 'SX', 'label' => 'Sodexo'],
            ['value' => 'JC', 'label' => 'JCB'],
            ['value' => 'AU', 'label' => 'Aura'],
            ['value' => 'CN', 'label' => 'Carnet'],
        ];
    }

    public static function getBrandName(string $brand)
    {
        $options = (new Brand)->toOptionArray();
        foreach ($options as $option) {
            if ($option['value'] == strtoupper($brand)) {
                return $option['label'];
            }
        }
        return $brand;
    }
}
