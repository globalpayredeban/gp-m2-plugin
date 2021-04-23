/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/totals',
        'Magento_Ui/js/model/messageList',
        'mage/translate',
        'https://cdn.globalpay.com.co/ccapi/sdk/payment_stable.min.js?no_cache=' + Math.random().toString(36).substring(7),
    ],
    function (Component, quote, customer, fullScreenLoader, totals, messageList, $t) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Globalpay_PaymentGateway/payment/globalpay_card',
                installment: '',
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'installment',
                    ]);
                return this;
            },

            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'installment': this.installment(),
                        'token': this.getToken(),
                    },
                };
            },

            getCode: function () {
                return this.item.method;
            },

            getPaymentConfig: function () {
                return window.checkoutConfig.payment[this.getCode()]
            },

            setToken: function (token) {
                window.checkoutConfig.payment[this.item.method]['token'] = token
            },

            getToken: function () {
                return this.getPaymentConfig().token;
            },

            getEnvironment: function () {
                return this.getPaymentConfig().environment;
            },

            getSupportedBrands: function () {
                return this.getPaymentConfig().brands ? this.getPaymentConfig().brands.join(',').toLowerCase() : '';
            },

            getCredentials: function () {
                return this.getPaymentConfig().credentials;
            },

            allowInstallments: function () {
                return this.getPaymentConfig().allow_installments;
            },

            showError: function (code) {
                let message = $t('Sorry, your payment could not be processed. (Code: %1)').replace('%1', code);
                fullScreenLoader.stopLoader();
                return this.messageContainer.addErrorMessage({message});
            },

            initPayment: function () {
                this.messageContainer = messageList;
                let context = this;

                try {
                    jQuery(document).ready(function () {
                        let credentials = context.getCredentials();
                        let environment = context.getEnvironment();

                        Payment.init(environment, credentials.application_code, credentials.application_key);
                        // Initialize PaymentForm instance
                        let card_form = jQuery('#globalpay_sdk_form');
                        card_form.PaymentForm('init');
                    });
                } catch (e) {
                    console.error(e);
                    setTimeout(function () {
                        context.initPayment();
                    }, 2000)
                }
                return true;
            },

            successPlaceOrder: function (context, token) {
                context.setToken(token);
                fullScreenLoader.stopLoader();
                this.placeOrder();
            },

            tokenize: function () {
                // Assign 'this' to variable for get access
                let context = this;

                // User data
                let guest_mail = quote.guestEmail || "foo@mail.com";
                let guest = {id: guest_mail, email: guest_mail};
                let client = customer.customerData.id !== undefined ? customer.customerData : guest;

                let card_form = jQuery('#globalpay_sdk_form');
                let card_to_save = card_form.PaymentForm('card');

                if (card_to_save == null) {
                    console.log('Invalid Card Data');
                } else {
                    fullScreenLoader.startLoader();
                    Payment.addCard(client.id, client.email, card_to_save, function (response) {
                        if (response.card.status === 'valid') {
                            context.successPlaceOrder(context, response.card.token);
                        } else {
                            context.showError(response.card.transaction_reference);
                        }
                    }, function (error) {
                        let errorType = error.error.type;
                        let errorTypeArr = errorType.split(' ');

                        // When card is already exists
                        if (errorTypeArr.length === 4 && errorTypeArr[3] && typeof errorTypeArr[3] == 'string' && errorTypeArr[3].length > 0) {
                            context.successPlaceOrder(context, errorTypeArr[3]);
                        } else {
                            let message = error.error.help ? error.error.help : error.error.description;
                            context.showError(message);
                        }
                    }, card_form);
                }
            },

            getTotal: function () {
                if (totals.totals()) {
                    return Math.round(parseFloat(totals.totals()['base_grand_total']));
                } else {
                    return Math.round(parseFloat(window.checkoutConfig.totalsData.base_grand_total));
                }
            },

            getAvailableInstallments: function () {
                let installments = [{'value': 1, 'text': `Total - $ ${this.getTotal()}`}];
                for (let i = 2; i <= 36; i++) {
                    let text = $t('%1 installments').replace('%1', i);
                    installments.push({'value': i, 'text': text});
                }
                return installments;
            }

        });
    }
);
