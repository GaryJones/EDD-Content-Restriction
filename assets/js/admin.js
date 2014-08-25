/*global jQuery, document, ajaxurl, window, console*/
jQuery(document).ready(function ($) {
    "use strict";
    $('body').on('change', 'select.edd_cr_download_id', function () {
        var $this = $(this), download_id = $this.val(), postData;

        if (parseInt(download_id) > 0) {
            $this.parent().next().find('.edd_cr_loading').show();

            postData = {
                action : 'edd_check_for_download_price_variations',
                download_id: download_id
            };

            $.ajax({
                type: "POST",
                data: postData,
                url: ajaxurl,
                success: function (response) {
                    if (response) {
                        $this.parent().next('td').find('.edd_cr_variable_none').hide();
                        $this.parent().next('td').find('select').remove();
                        $(response).appendTo($this.parent().next('td'));
                    } else {
                        $this.parent().next('td').find('select').remove();
                        $this.parent().next('td').find('.edd_cr_variable_none').show();
                    }
                }
            }).fail(function (data) {
                if (window.console && window.console.log) {
                    console.log(data);
                }
            });

            $this.parent().next().find('.edd_cr_loading').hide();
        }
    });
});