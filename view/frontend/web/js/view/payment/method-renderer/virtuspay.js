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
        'mage/translate',
        'inputmask',
    ],
    function (Component,$,ko,storage,$t) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'VirtusPay_Magento2/payment/virtuspay'
            },
            initObservable: function () {
                this._super();
                jQuery(document).ready(() => {
                    jQuery("#virtuspay-dob").mask("00/00/0000");
                })
                return this;
            },
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'installments': jQuery('#'+this.getCode() + '-installments').val(),
                        'quoteid': jQuery('#virtuspay-quote-id').val(),
                        'preapproved':  window.virtuspay.preApproved,
                        'dob': jQuery('#virtuspay-dob').val()
                    }
                };
            },
            dobMask: function () {
                jQuery("#virtuspay-dob").mask("00/00/0000");
                return '';
            },
            dobValidate: function () {
                if(jQuery("#virtuspay-dob").val().length != 10) {
                    return false;
                }
                let date = jQuery("#virtuspay-dob").val(); // 10/10/1980
                date = date.substr(6,9) + '-' + date.substr(3,4) + '-' +  date.substr(0,1);

                var before = new Date(date);
                console.log(before.getFullYear());
                var now = new Date();
                if (before < now) {
                    return false;
                }
                return true;
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
                        window.virtuspay.cet = "";
                        var json = JSON.parse(msg.response);
                        if (json.preapproved === false) {
                            window.virtuspay.preApproved = false;
                            var txt = 'No pre-approved conditions found.';
                            jQuery('.virtuspay-message').html($t(txt));
                            jQuery('.virtuspay-consult-installments').hide();
                            jQuery('.virtuspay-message').show();
                            jQuery('#virtuspay-quote-id').val(json.id);
                        } else if (json.preapproved === true) {
                            window.virtuspay.preApproved = true;
                            jQuery('#virtuspay-quote-id').val(json.id);
                            window.virtuspay.total_amount = json.total_ammount;
                            window.virtuspay.cet = json.cet;
                            window.virtuspay.preApproved = json.preapproved;
                            if(json.preapproved === true) {
                                jQuery('#virtuspay-placeorder').show();
                            }
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
