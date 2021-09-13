/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'ko',
        'mage/storage',
        'mage/translate'
    ],
    function (Component,$,ko,storage,$t) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'VirtusPay_Magento2/payment/virtuspay'
            },
            initObservable: function () {
                this._super();
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
            getDue: function () {
                return window.checkoutConfig.payment.virtuspayboleto.due;
            },
            changeSelect: function () {
                document.getElementById('virtuspay-installments').addEventListener("change",function () {
                    event.preventDefault();
                    jQuery('#virtuspay-installment-details').html('' +
                        'Valor total: R$' + window.virtuspay_installments[$('#virtuspay-installments').val()].total +
                        '');
                    jQuery('#virtuspay-installment-details').show();
                });
            },
            getQuote: function () {
                jQuery('body').trigger('processStart');
                storage.post(
                    'rest/V1/virtuspay/quote',
                    "",
                    true
                ).done(function (msg) {
                    if (msg) {
                        console.log(msg);
                        window.obJson = JSON.parse(msg);
                        var json = window.obJson;
                        if (json.preapproved === false) {
                            var txt = 'No pre-approved conditions found.';
                            jQuery('.virtuspay-message').html($t(txt));
                            jQuery('.virtuspay-consult-installments').hide();
                            jQuery('.virtuspay-message').show();
                        } else if (json.preapproved === true) {
                            jQuery('#virtuspay-quote-id').val(json.id);
                            window.virtuspay.total_amount = json.total_ammount;
                            window.virtuspay.cet = json.cet;
                            window.virtuspay_installments = [];
                            json.installments.forEach(function (installment) {
                                window.virtuspay_installments[installment.installment] = {};
                                window.virtuspay_installments[installment.installment].down_payment = installment.down_payment;
                                window.virtuspay_installments[installment.installment].outstanding_balance = installment.outstanding_balance;
                                window.virtuspay_installments[installment.installment].total = installment.total;
                                window.virtuspay_installments[installment.installment].interest = installment.interest;
                                jQuery('#virtuspay-installments').append($('<option>', {
                                    value: installment.installment,
                                    text: installment.installment + ' parcelas de R$' + installment.down_payment
                                }));
                            });
                            jQuery('.virtuspay-consult-installments').hide();
                            jQuery('.virtuspay-installments-list').show();
                        }
                    }
                    jQuery('body').trigger('processStop');
                }).fail(function () {
                    jQuery('body').trigger('processStop');
                });

            }
        });
    }
);
