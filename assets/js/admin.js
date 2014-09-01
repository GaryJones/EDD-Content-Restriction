/*global jQuery, document, ajaxurl, window, console*/
jQuery(document).ready(function ($) {
    "use strict";
    $('body').on('change', 'select.edd_cr_download', function () {
        var $this = $(this), download_id = $this.val(), key = $this.data('key'), postData;

        if (parseInt(download_id) > 0) {
            $this.parent().next('td').find('select').remove();
            $this.parent().next().find('.edd_cr_loading').show();

            postData = {
                action : 'edd_cr_check_for_download_price_variations',
                download_id: download_id,
                key: key
            };

            $.ajax({
                type: "POST",
                data: postData,
                url: ajaxurl,
                success: function (response) {
                    if (response) {
                        $this.parent().next('td').find('.edd_cr_variable_none').hide();
                        $(response).appendTo($this.parent().next('td'));
                    } else {
                        $this.parent().next('td').find('.edd_cr_variable_none').show();
                    }
                }
            }).fail(function (data) {
                if (window.console && window.console.log) {
                    console.log(data);
                }
            });

            $this.parent().next().find('.edd_cr_loading').hide();
        } else {
            $this.parent().next('td').find('.edd_cr_variable_none').show();   
            $this.parent().next('td').find('.edd_price_options_select').remove();   
        }
    });
});
