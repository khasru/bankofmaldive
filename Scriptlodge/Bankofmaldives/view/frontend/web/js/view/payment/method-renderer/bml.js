/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Scriptlodge_Bankofmaldives/js/form-builder',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/place-order',
    'Magento_Customer/js/customer-data'
], function (
    $,
    Component,
    quote,
    customer,
    urlBuilder,
    storage,
    formBuilder,
    errorProcessor,
    fullScreenLoader,
    placeOrderService,
    customerData
) {
    'use strict';

    return Component.extend({

        defaults: {
            template: 'Scriptlodge_Bankofmaldives/payment/bml-form',
            redirectAfterPlaceOrder: false
        },

        /** Open window with  */
        showAcceptanceWindow: function (data, event) {
            window.open(
                $(event.target).attr('href'),
                'olcwhatispaypal',
                'toolbar=no, location=no,' +
                ' directories=no, status=no,' +
                ' menubar=no, scrollbars=yes,' +
                ' resizable=yes, ,left=0,' +
                ' top=0, width=400, height=350'
            );

            return false;
        },

        /** Returns payment acceptance mark image path */
        getPaymentAcceptanceMarkSrc: function () {
            return window.checkoutConfig.payment[this.getCode()].paymentAcceptanceMarkSrc;
        },

        getTermsAndConditions: function() {
            return window.checkoutConfig.payment[this.getCode()].paymentTermsAndConditions;
        },


        afterPlaceOrder: function () {

        var quoteid=quote.getQuoteId();
            $.mage.redirect(
                window.checkoutConfig.payment[this.getCode()].redirectDataUrl+'?quote_id='+quoteid
            );
            return false;
        },

        /**
         * Rewrites place order deferred object with bml guest cart service handler
         * as a workaround for issue with customer sections flush on regular place order action
         * @returns {*}
         */
        getPlaceOrderDeferredObject: function () {
            var self = this;

            if (customer.isLoggedIn()) {
                return this._super();
            }

            return $.when(
                placeOrderService(
                    urlBuilder.createUrl('/bml-guest-carts/:quoteId/payment-information', {
                        quoteId: quote.getQuoteId()
                    }),
                    {
                        cartId: quote.getQuoteId(),
                        billingAddress: quote.billingAddress(),
                        paymentMethod: this.getData(),
                        email: quote.guestEmail
                    },
                    self.messageContainer
                )
            );
        },

        /**
         * Payment method code getter
         * @returns {String}
         */
        getCode: function () {
            return 'bml';
        }
    });
});
