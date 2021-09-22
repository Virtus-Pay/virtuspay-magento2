define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'VirtusPay_Magento2/js/model/boleto-validator'
    ],
    function (Component, additionalValidators, boletoValidator) {
        'use strict';
        additionalValidators.registerValidator(boletoValidator);
        return Component.extend({});
    }
);
