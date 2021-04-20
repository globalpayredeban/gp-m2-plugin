/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list',
        'https://code.jquery.com/jquery-1.11.3.min.js',
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        const config = window.checkoutConfig.payment;

        if (config.globalpay_card.is_active) {
            rendererList.push(
                {
                    type: 'globalpay_card',
                    component: 'Globalpay_PaymentGateway/js/view/payment/method-renderer/globalpay_card'
                }
            );
        }
        if (config.globalpay_ltp.is_active) {
            rendererList.push(
                {
                    type: 'globalpay_ltp',
                    component: 'Globalpay_PaymentGateway/js/view/payment/method-renderer/globalpay_ltp'
                }
            );
        }
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
