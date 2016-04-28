define(function(require) {
    'use strict';

    var AuthorizedCreditCardComponent;
    var _ = require('underscore');
    var $ = require('jquery');
    var mediator = require('oroui/js/mediator');
    var CreditCardComponent = require('orob2bpayment/js/app/components/credit-card-component');

    AuthorizedCreditCardComponent = CreditCardComponent.extend({
        /**
         * @property {Object}
         */
        authorizedOptions: {
            selectors: {
                differentCard: '[data-different-card]',
                authorizedCard: '[data-authorized-card]',
                differentCardHandle: '[data-different-card-handle]',
                authorizedCardHandle: '[data-authorized-card-handle]'
            }
        },

        /**
         * @property {Boolean}
         */
        paymentValidationRequiredComponentState: false,

        /**
         * @property {jQuery}
         */
        $authorizedCard: null,

        /**
         * @property {jQuery}
         */
        $differentCard: null,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            AuthorizedCreditCardComponent.__super__.initialize.call(this, options);

            this.options = _.defaults(options || {}, this.options);

            this.$authorizedCard = this.$el.find(this.authorizedOptions.selectors.authorizedCard);
            this.$differentCard = this.$el.find(this.authorizedOptions.selectors.differentCard);

            this.$el
                .on('click', this.authorizedOptions.selectors.authorizedCardHandle, _.bind(this.showAuthorizedCard, this))
                .on('click', this.authorizedOptions.selectors.differentCardHandle, _.bind(this.showDifferentCard, this));
        },

        showDifferentCard: function() {
            this.$authorizedCard
                .css('position', 'absolute');

            this.$differentCard.show('slide', {direction: 'right'});
            this.$authorizedCard.hide('slide', {direction: 'left'}, (function() {
                this.$authorizedCard
                    .css('position', 'relative');
            }).bind(this));

            this.setPaymentValidateRequired(true);

            return false;
        },

        showAuthorizedCard: function() {
            this.$authorizedCard
                .css('position', 'absolute');

            this.$authorizedCard.show('slide', {direction: 'left'});
            this.$differentCard.hide('slide', {direction: 'right'}, (function() {
                this.$authorizedCard
                    .css('position', 'relative');
            }).bind(this));

            this.setPaymentValidateRequired(false);

            return false;
        },

        beforeTransit: function(eventData) {
            if (this.getPaymentValidateRequired()) {
                AuthorizedCreditCardComponent.__super__.beforeTransit.call(this, eventData);
            }
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }

            this.$el
                .off('click', this.authorizedOptions.selectors.authorizedCardHandle, _.bind(this.showAuthorizedCard, this))
                .off('click', this.authorizedOptions.selectors.differentCardHandle, _.bind(this.showDifferentCard, this));

            AuthorizedCreditCardComponent.__super__.dispose.call(this);
        }
    });

    return AuthorizedCreditCardComponent;
});
