/**
 * Lead Generation Form - Import & Export Script
 */

jQuery(document).ready(function($) {
	// Export Form Action
	$('#wlgf-export-btn').on('click', function(e) {
		e.preventDefault();
		const formId = $('#wlgf-form-list').val();

		if (!formId || formId === '0') {
			$('#wlgf-form-list').focus();
			return false;
		}

		const $btn = $(this);
		$btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Generating...');

		$.ajax({
			type: 'POST',
			url: ImportExport.ajaxUrl,
			data: {
				action: 'wlgf_import_export',
				do: 'export',
				wlgf_form_id: formId,
				nonce: ImportExport.nonce
			},
			success: function(result) {
				$('#wlgf-form-export').html(result);
				$('#wlgf-export-output-wrap').removeClass('d-none');
			},
			complete: function() {
				$btn.prop('disabled', false).html('<i class="fa-solid fa-code"></i> Generate Export Payload');
			}
		});
	});

	// Copy Payload to Clipboard
	$('#wlgf-copycode').on('click', function(e) {
		e.preventDefault();
		const payloadText = $('#wlgf-form-code').val();

		if (!payloadText) {
			return false;
		}

		if (navigator.clipboard && window.isSecureContext) {
			navigator.clipboard.writeText(payloadText);
		} else {
			const $temp = $('<textarea>');
			$('body').append($temp);
			$temp.val(payloadText).select();
			document.execCommand('copy');
			$temp.remove();
		}

		$('#wlgf-copy-success').removeClass('d-none');
		setTimeout(function() {
			$('#wlgf-copy-success').addClass('d-none');
		}, 3000);
	});

	// Import Form Action
	$('#wlgf-import-btn').on('click', function(e) {
		e.preventDefault();
		const formName = $('#wlgf-import-name').val().trim();
		const formData = $('#wlgf-import-data').val().trim();

		if (!formName) {
			$('#wlgf-import-name').focus();
			return false;
		}
		if (!formData) {
			$('#wlgf-import-data').focus();
			return false;
		}

		try {
			JSON.parse(formData);
		} catch (err) {
			$('#wlgf-import-result').html('<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-circle-exclamation"></i> <span>Invalid JSON format. Please verify the schema payload.</span></div>');
			return false;
		}

		const $btn = $(this);
		$btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Importing...');
		$('#wlgf-import-result').empty();

		$.ajax({
			type: 'POST',
			url: ImportExport.ajaxUrl,
			data: {
				action: 'wlgf_import_export',
				do: 'import',
				wlgf_form_name: formName,
				wlgf_form_data: formData,
				nonce: ImportExport.nonce
			},
			success: function(response) {
				if (response && response.success) {
					$('#wlgf-import-result').html('<div class="wlgf-alert wlgf-alert--success"><i class="fa-solid fa-circle-check"></i> <span>' + $('<div>').text(response.data.message).html() + '</span></div>');
					$('#wlgf-import-name').val('');
					$('#wlgf-import-data').val('');
				} else {
					const msg = (response && response.data && response.data.message) ? response.data.message : 'Import failed.';
					$('#wlgf-import-result').html('<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-circle-exclamation"></i> <span>' + $('<div>').text(msg).html() + '</span></div>');
				}
			},
			error: function() {
				$('#wlgf-import-result').html('<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-circle-exclamation"></i> <span>AJAX error occurred during import.</span></div>');
			},
			complete: function() {
				$btn.prop('disabled', false).html('<i class="fa-solid fa-arrow-down-to-bracket"></i> Import & Create Form');
			}
		});
	});

	// Combine Forms Action
	$('#wlgf-combine-btn').on('click', function(e) {
		e.preventDefault();
		const formOneId = $('#wlgf-form-one').val();
		const formTwoId = $('#wlgf-form-two').val();

		if (!formOneId || formOneId === '0') {
			$('#wlgf-form-one').focus();
			return false;
		}
		if (!formTwoId || formTwoId === '0') {
			$('#wlgf-form-two').focus();
			return false;
		}
		if (formOneId === formTwoId) {
			$('#wlgf-combine-result').html('<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-circle-exclamation"></i> <span>Please select two different forms to combine.</span></div>');
			return false;
		}

		const $btn = $(this);
		$btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Merging...');
		$('#wlgf-combine-result').empty();

		$.ajax({
			type: 'POST',
			url: ImportExport.ajaxUrl,
			data: {
				action: 'wlgf_import_export',
				do: 'combine',
				wlgf_form_one_id: formOneId,
				wlgf_form_two_id: formTwoId,
				nonce: ImportExport.nonce
			},
			success: function(response) {
				if (response && response.success) {
					$('#wlgf-combine-result').html('<div class="wlgf-alert wlgf-alert--success"><i class="fa-solid fa-circle-check"></i> <span>' + $('<div>').text(response.data.message).html() + '</span></div>');
				} else {
					const msg = (response && response.data && response.data.message) ? response.data.message : 'Merge failed.';
					$('#wlgf-combine-result').html('<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-circle-exclamation"></i> <span>' + $('<div>').text(msg).html() + '</span></div>');
				}
			},
			error: function() {
				$('#wlgf-combine-result').html('<div class="wlgf-alert wlgf-alert--danger"><i class="fa-solid fa-circle-exclamation"></i> <span>AJAX error occurred during combine.</span></div>');
			},
			complete: function() {
				$btn.prop('disabled', false).html('<i class="fa-solid fa-plus"></i> Merge Forms into New Form');
			}
		});
	});
});