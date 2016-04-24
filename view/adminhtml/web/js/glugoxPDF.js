/**
 * This file is part of Glugox.
 *
 * (c) Glugox <glugox@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";


    $.widget('mage.glugoxPDF', {
        /**
         * Options common to all instances of this widget.
         * @type {Object}
         */
        options: {
            /**
             * URL of the pdf grid.
             * @type {String}
             */
            gridUrl: ''
        },
        /**
         * Bind event handler for the action when admin clicks "Save & Activate" button.
         * @private
         */
        _create: function () {
            var that = this;
        },
    });


    window.glugoxPDF = {
        /**
         * Options
         * @type {Object}
         */
        options: {
        }

    }

});