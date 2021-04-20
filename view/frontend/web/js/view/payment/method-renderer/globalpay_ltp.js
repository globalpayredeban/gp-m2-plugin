/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/redirect-on-success',
        'mage/url',
        'mage/translate'
    ],
    function (Component, redirectOnSuccessAction, url, $t) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Globalpay_PaymentGateway/payment/globalpay_ltp'
            },
            afterPlaceOrder: function () {
                redirectOnSuccessAction.redirectUrl = url.build("redirectlinktopay/placeorder/placeorder");
                this.redirectAfterPlaceOrder = true;
                redirectOnSuccessAction.execute();
            }
        });
    }
);
