/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

define([
    'jquery',
    'priceBox'
], function (
    $
) {
    'use strict';

    var globalOptions = {
        offerConfig: {},
        offerSelector: '.product-option-offer input[type="checkbox"]',
        priceHolderSelector: '.price-box'
    };

    $.widget('mage.catalogProductOptionOffer', {
        options: globalOptions,
        offers: null,

        _create: function createCatalogProductOptionOffer() {
            this.offers = $(this.options.offerSelector, this.element);

            this.offers.on('change', this.onOfferChanged.bind(this));
        },

        _init: function initCatalogProductOptionOffer() {
            $(this.options.offerSelector, this.element).trigger('change');
        },

        onOfferChanged: function onOfferChanged() {
            var self = this;
            var changes = {};

            this.offers.each(function(key, element) {
                var offer = $(element);
                var offerId = offer.data('offer-id');
                var offerName = offer.prop('id');

                changes[offerName] = offer.is(":checked") && self.options.offerConfig[offerId] ?
                    self.options.offerConfig[offerId].prices : {};

                if (offer.is(":checked")) {
                    $.each(self.options.offerConfig[offerId].options, function(optionId, optionValueIds) {
                        $('#select_' + optionId).each(function() {
                            if (optionValueIds === 0) {
                                $(this).attr('data-offer', true);
                            } else {
                                $(this).attr('disabled', true);
                            }
                            if ($(this).val()) {
                                var optionHash = $(this).prop('name');
                                if ($(this).prop('multiple')) {
                                    $.each($(this).val(), function(key, value) {
                                        var optionValueHash = optionHash + '##' + value;
                                        changes[optionValueHash] = {};
                                    });
                                } else {
                                    changes[optionHash] = {};
                                }
                            }
                        });
                        $('#options_' + optionId + '_text').each(function() {
                            $(this).attr('disabled', true);
                        });
                        $('#options_' + optionId + '_month').each(function() {
                            $(this).attr('disabled', true);
                        });
                        $('#options_' + optionId + '_day').each(function() {
                            $(this).attr('disabled', true);
                        });
                        $('#options_' + optionId + '_year').each(function() {
                            $(this).attr('disabled', true);
                        });
                        $('#options-' + optionId + '-list').find('input[type="checkbox"]').each(function() {
                            if (optionValueIds === 0) {
                                $(this).attr('data-offer', true);
                            } else {
                                $(this).attr('disabled', true);
                            }
                            if ($(this).is(':checked')) {
                                var optionHash = $(this).prop('name') + '##' + $(this).val();
                                changes[optionHash] = {};
                            }
                        });
                        $('#options-' + optionId + '-list').find('input[type="radio"]').each(function() {
                            if (optionValueIds === 0) {
                                $(this).attr('data-offer', true);
                            } else {
                                $(this).attr('disabled', true);
                            }
                            if ($(this).is(':checked')) {
                                var optionHash = $(this).prop('name');
                                changes[optionHash] = {};
                            }
                        });
                    });
                } else {
                    $.each(self.options.offerConfig[offerId].options, function(optionId, optionValueIds) {
                        $('#select_' + optionId).each(function() {
                            if (optionValueIds === 0) {
                                $(this).removeAttr('data-offer');
                            } else {
                                $(this).removeAttr('disabled');
                            }
                            if ($(this).val()) {
                                $(this).trigger('change');
                            }
                        });
                        $('#options_' + optionId + '_text').each(function() {
                            $(this).removeAttr('disabled');
                        });
                        $('#options_' + optionId + '_month').each(function() {
                            $(this).removeAttr('disabled');
                        });
                        $('#options_' + optionId + '_day').each(function() {
                            $(this).removeAttr('disabled');
                        });
                        $('#options_' + optionId + '_year').each(function() {
                            $(this).removeAttr('disabled');
                        });
                        $('#options-' + optionId + '-list').find('input[type="checkbox"]').each(function() {
                            if (optionValueIds === 0) {
                                $(this).removeAttr('data-offer');
                            } else {
                                $(this).removeAttr('disabled');
                            }
                            if ($(this).is(':checked')) {
                                $(this).trigger('change');
                            }
                        });
                        $('#options-' + optionId + '-list').find('input[type="radio"]').each(function() {
                            if (optionValueIds === 0) {
                                $(this).removeAttr('data-offer');
                            } else {
                                $(this).removeAttr('disabled');
                            }
                            if ($(this).is(':checked')) {
                                $(this).trigger('change');
                            }
                        });
                    });
                }
            });

            $(this.options.priceHolderSelector).trigger('updatePrice', changes);
        }
    });

    return $.mage.catalogProductOptionOffer;
});
