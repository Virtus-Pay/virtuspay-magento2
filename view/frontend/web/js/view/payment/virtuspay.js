define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'virtuspay',
                component: 'VirtusPay_Magento2/js/view/payment/method-renderer/virtuspay'
            }
        );
        return Component.extend({});
    }
);
