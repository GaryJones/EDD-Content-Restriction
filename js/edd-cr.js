jQuery(document).ready(function($) {
	$('#edd_cr_download_id').change(function() {
		var selected_download = $('option:selected', this).val();
		if( selected_download != 0) {
			var edd_cr_nonce = $('#edd-cr-nonce').val();
			var data = {
				action: 'edd_cr_check_for_variations',
				download_id: selected_download,
				nonce: edd_cr_nonce
			}
			$('#edd_cr_loading').show();
			$.post(ajaxurl, data, function(response) {
				$('#edd_download_variables').html(response);
				$('#edd_cr_loading').hide();
			});
		} else {
			$('#edd_download_variables').html('');
			$('#edd_cr_loading').hide();
		}
	});
});