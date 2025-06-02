/**
 * @author      Andreas Knollmann
 * @copyright   2014-2025 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

define([
    'jquery'
], function ($) {
    'use strict';

    var priceOptionsWidgetMixin = {
        _onOptionChanged: function(event) {
            var option = $(event.target);
            if (option.attr('data-offer') !== 'true') {
                return this._super(event);
            }
        }
    };

    return function (targetWidget) {
        $.widget('mage.priceOptions', targetWidget, priceOptionsWidgetMixin);

        return $.mage.priceOptions;
    };
});
