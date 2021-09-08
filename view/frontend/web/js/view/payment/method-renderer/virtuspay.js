/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery'
    ],
    function (Component,$) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'VirtusPay_Magento2/payment/virtuspay'
            },
            initObservable: function () {
                this._super()
                    .observe([
                        'boletofullname',
                        'boletodocument'
                    ]);

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
            getInstruction: function() {
                return window.checkoutConfig.payment.virtuspayboleto.instruction;
            },
            getDue: function() {
                return window.checkoutConfig.payment.virtuspayboleto.due;
            },
            getFullName: function() {
                return window.checkoutConfig.payment.virtuspayboleto.fullname;
            },
            getTaxVat: function() {
                return window.checkoutConfig.payment.virtuspayboleto.taxvat;
            },
            getQuote: function() {
                $.ajax({
                    url: '/rest/V1/virtuspay/quote',
                    type: 'GET',
                    beforeSend: function(xhr){
                        //Empty to remove magento's default handler
                    }
                }).done(function (msg) {
                    if (msg) {
                        $('span#notafiscal_txt').html($('input#notafiscal_input').val());
                        $('input#notafiscal_input').hide();
                        $('button#notafiscal_post_button').hide();
                        $('span#notafiscal_txt').show();
                        $('button#notafiscal_edit_button').show();
                    } else {
                        $('span#notafiscal_error').html($t('Error saving url.'));
                        $('span#notafiscal_error').show();
                    }
                }).fail(function (jqXHR, textStatus, msg) {
                    $('span#notafiscal_error').html($t('Error saving url: ' + msg));
                    $('span#notafiscal_error').show();
                });
            }
        });
    }
);
