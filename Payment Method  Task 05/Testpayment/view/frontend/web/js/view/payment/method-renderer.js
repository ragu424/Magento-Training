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
                type: 'testpayment',
                component: 'Ragu_Testpayment/js/view/payment/method-renderer/testpayment'
            }
        );
        return Component.extend({});
    }
);