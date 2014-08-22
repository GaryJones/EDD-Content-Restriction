/*global jQuery, document, ajaxurl*/
jQuery(document).ready(function ($) {
    "use strict";
    $('#edd_cr_download_id').change(function () {
        var selected_download = $('option:selected', this).val(), edd_cr_nonce, data;

        if (selected_download !== 0) {
            edd_cr_nonce = $('#edd-cr-nonce').val();
            data = {
                action: 'edd_cr_check_for_variations',
                download_id: selected_download,
                nonce: edd_cr_nonce
            };

            $('#edd_cr_loading').show();

            $.post(ajaxurl, data, function (response) {
                $('#edd_download_variables').html(response);
                $('#edd_cr_loading').hide();
            });
        } else {
            $('#edd_download_variables').html('');
            $('#edd_cr_loading').hide();
        }
    });
});