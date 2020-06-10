/**
 * NOTICE OF LICENSE.
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    e-satisfaction.com
 * @copyright 2020 e-satisfaction.com
 * @license   https://opensource.org/licenses
 * @version   1.1.1
 */
(function ($) {
    $(document).on('ready', function () {
        // Initialize display of manual statuses
        showHideManualSettings();

        // Listen on change
        $('input[type=radio][name=manual_send]').on('change', function () {
            showHideManualSettings();
        });

        function showHideManualSettings() {
            if ($('input[type=radio][name=manual_send]:checked').val() === '1') {
                $('#fieldset_3_3').slideDown(400);
                $('#fieldset_4_4').slideDown(400);
                $('#fieldset_5_5').slideDown(400);
            } else {
                $('#fieldset_3_3').slideUp(400);
                $('#fieldset_4_4').slideUp(400);
                $('#fieldset_5_5').slideUp(400);
            }
        }
    });
})(jQuery);
