/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
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
                type: 'zarinpay',
                component: 'Chalaksoft_Zarinpay/js/view/payment/method-renderer/zarinpay-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({
            defaults: {
                redirectAfterPlaceOrder: true
            },

            afterPlaceOrder: function (data, event) {
                window.location.replace('zarinpay/index/index');

            }


        });

    }
);