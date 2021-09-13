/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'ko',
        'mage/storage'
    ],
    function (Component,$,ko,storage) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'VirtusPay_Magento2/payment/virtuspay'
            },
            initObservable: function () {
                this._super();


                //     .observe([
                //         'boletofullname',
                //         'boletodocument'
                //     ]);
                //
                // document.getElementById("consult_installments").addEventListener("click", function (event) {
                //     event.preventDefault();
                //     this.getQuote();
                // });
                return this;
            },
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'boletofullname': jQuery('#'+this.getCode() + '_boletofullname').val(),
                        'boletodocument': jQuery('#'+this.getCode() + '_boletodocument').val(),
                        'antifraud_token': jQuery('#antifraud_token').val()
                    }
                };
            },
            getDue: function() {
                return window.checkoutConfig.payment.virtuspayboleto.due;
            },
            getQuote: function() {
                jQuery('body').trigger('processStart');
                storage.post(
                    'rest/V1/virtuspay/quote',
                    "",
                    true
                ).done(function (msg) {
                    if (msg) {
                        let objecto = JSON.parse(msg);

                    }
                    jQuery('body').trigger('processStop');
                }).fail(function (msg) {
                    jQuery('body').trigger('processStop');
                });

            }
        });
    }
);
