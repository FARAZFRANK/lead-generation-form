/**
 * Lead Generation Form - Manage Forms Script
 * Version: 3.4.8
 */

(function($) {
	'use strict';

	let wlgf_datatable = null;
	let wlgf_pending_delete_id = null;

	window.WLGF_ManageForms = {
		init: function() {
			if ($('#forms-tables').length) {
				wlgf_datatable = $('#forms-tables').DataTable({
					order: [[0, 'asc']],
					responsive: true,
					columnDefs: [
						{ targets: 0, type: 'num' },
						{ targets: [2, 3, 4], type: 'num' },
						{ targets: [-1, -2], orderable: false },
						{ targets: '_all', className: 'dt-left' }
					],
					language: {
						search: '_INPUT_',
						searchPlaceholder: 'Search forms...'
					}
				});
			}

			// Delete Modal Cancel Handlers
			$('#wlgf-cancel-delete-btn, #wlgf-delete-modal').on('click', function(e) {
				if (e.target === this) {
					$('#wlgf-delete-modal').removeClass('wlgf-modal-overlay--active');
					wlgf_pending_delete_id = null;
				}
			});

			// Clone Modal Close Handlers
			$('.wlgf-clone-modal-close-btn, #wlgf-clone-modal').on('click', function(e) {
				if (e.target === this || $(this).hasClass('wlgf-clone-modal-close-btn') || $(this).closest('.wlgf-clone-modal-close-btn').length) {
					$('#wlgf-clone-modal').removeClass('wlgf-modal-overlay--active');
				}
			});

			// Clone Modal Copy Shortcode Button
			$('#wlgf-clone-modal-copy-btn').on('click', function() {
				window.WLGF_ManageForms.copyModalShortcode();
			});

			// Escape Key Closes Any Open Modal
			$(document).on('keydown', function(e) {
				if (e.key === 'Escape' || e.keyCode === 27) {
					$('#wlgf-delete-modal, #wlgf-clone-modal').removeClass('wlgf-modal-overlay--active');
					wlgf_pending_delete_id = null;
				}
			});

			// Modal Confirm Delete Handler
			$('#wlgf-confirm-delete-btn').on('click', function() {
				if (!wlgf_pending_delete_id) {
					return;
				}

				const formId = wlgf_pending_delete_id;
				const $btn = $(this);
				$btn.prop('disabled', true).text('Deleting...');

				$.ajax({
					type: 'POST',
					url: ManageForms.ajaxUrl,
					data: {
						action: 'wlgf_manage_form_action',
						do: 'delete',
						nonce: ManageForms.nonce,
						wlgf_form_id: formId
					},
					success: function() {
						if (wlgf_datatable) {
							wlgf_datatable.row($('tr#' + formId)).remove().draw(false);
						} else {
							$('tr#' + formId).remove();
						}
						// Update Total Forms metric
						const $totalStat = $('#wlgf-total-forms-stat');
						if ($totalStat.length) {
							const currentCount = parseInt($totalStat.text().replace(/[^0-9]/g, ''), 10) || 0;
							if (currentCount > 0) {
								$totalStat.text(currentCount - 1);
							}
						}
						$('#wlgf-delete-modal').removeClass('wlgf-modal-overlay--active');
						wlgf_pending_delete_id = null;
					},
					complete: function() {
						$btn.prop('disabled', false).html('<i class="fa-regular fa-trash-can"></i> Delete Form');
					}
				});
			});
		},

		copy: function(wlgf_form_id) {
			const shortcodeText = '[WLFG id="' + wlgf_form_id + '"]';

			if (navigator.clipboard && window.isSecureContext) {
				navigator.clipboard.writeText(shortcodeText);
			} else {
				const tempInput = document.createElement('input');
				tempInput.value = shortcodeText;
				document.body.appendChild(tempInput);
				tempInput.select();
				document.execCommand('copy');
				document.body.removeChild(tempInput);
			}

			const $tooltip = $('#wlgf-copied-' + wlgf_form_id);
			$tooltip.addClass('wlgf-code-pill__copied-tooltip--active');
			setTimeout(function() {
				$tooltip.removeClass('wlgf-code-pill__copied-tooltip--active');
			}, 2000);
			return false;
		},

		copyModalShortcode: function() {
			const shortcodeText = $('#wlgf-clone-modal-shortcode').text().trim();
			if (!shortcodeText) {
				return;
			}

			function triggerSuccessUI() {
				const $btn = $('#wlgf-clone-modal-copy-btn');
				const $label = $('#wlgf-clone-modal-copy-text');
				const originalText = $btn.data('original-label') || $label.text() || 'Copy Shortcode';
				$btn.data('original-label', originalText);

				$btn.addClass('copied');
				$btn.find('i').removeClass('fa-regular fa-copy').addClass('fa-solid fa-check');
				$label.text('Copied!');

				setTimeout(function() {
					$btn.removeClass('copied');
					$btn.find('i').removeClass('fa-solid fa-check').addClass('fa-regular fa-copy');
					$label.text(originalText);
				}, 2000);
			}

			function fallbackCopy() {
				const tempInput = document.createElement('input');
				tempInput.value = shortcodeText;
				document.body.appendChild(tempInput);
				tempInput.select();
				try {
					document.execCommand('copy');
				} catch(e) {}
				document.body.removeChild(tempInput);
				triggerSuccessUI();
			}

			if (navigator.clipboard && window.isSecureContext) {
				navigator.clipboard.writeText(shortcodeText).then(function() {
					triggerSuccessUI();
				}).catch(function() {
					fallbackCopy();
				});
			} else {
				fallbackCopy();
			}
		},

		clone: function(wlgf_form_id, triggerBtn) {
			const $btn = triggerBtn ? $(triggerBtn) : $('tr#' + wlgf_form_id + ' button[onclick*="clone"]');
			const originalHtml = $btn.html();
			$btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');

			$.ajax({
				type: 'POST',
				url: ManageForms.ajaxUrl,
				data: {
					action: 'wlgf_manage_form_action',
					do: 'clone',
					nonce: ManageForms.nonce,
					wlgf_form_id: wlgf_form_id
				},
				success: function(response) {
					let rowHtml = '';
					let cloneData = null;

					if (typeof response === 'object' && response !== null) {
						if (response.success && response.data) {
							rowHtml = response.data.html;
							cloneData = response.data;
						} else if (response.html) {
							rowHtml = response.html;
							cloneData = response;
						}
					} else if (typeof response === 'string') {
						try {
							const parsed = JSON.parse(response);
							if (parsed && (parsed.data || parsed.html)) {
								cloneData = parsed.data || parsed;
								rowHtml = cloneData.html || '';
							}
						} catch(e) {
							rowHtml = response;
						}
					}

					if (rowHtml) {
						const $newRow = $(rowHtml);

						// Fallback if cloneData wasn't provided via JSON
						if (!cloneData || !cloneData.clone_name) {
							cloneData = {
								clone_id: $newRow.attr('data-clone-id') || $newRow.attr('id'),
								clone_name: $newRow.attr('data-clone-name') || $newRow.find('td:nth-child(2) strong').text().trim(),
								shortcode: $newRow.attr('data-clone-shortcode') || '[WLFG id="' + ($newRow.attr('id') || '') + '"]',
								original_name: $newRow.attr('data-original-name') || $('tr#' + wlgf_form_id + ' td:nth-child(2) strong').text().trim(),
								edit_url: $newRow.attr('data-edit-url') || $newRow.find('a[href*="wlgf-form-generator"]').attr('href')
							};
						}

						// Insert into table
						if (wlgf_datatable) {
							const addedRow = wlgf_datatable.row.add($newRow).draw(false);
							if (addedRow && typeof addedRow.index === 'function') {
								const rowIndex = addedRow.index();
								const pageLen = wlgf_datatable.page.len();
								if (pageLen > 0) {
									const targetPage = Math.floor(rowIndex / pageLen);
									wlgf_datatable.page(targetPage).draw(false);
								}
							}
						} else {
							$('tbody#wlgf-tbody').append($newRow);
						}

						// Update Total Forms metric
						const $totalStat = $('#wlgf-total-forms-stat');
						if ($totalStat.length) {
							const currentCount = parseInt($totalStat.text().replace(/[^0-9]/g, ''), 10) || 0;
							$totalStat.text(currentCount + 1);
						}

						// Highlight the new row
						const $targetRow = $('tr#' + cloneData.clone_id);
						if ($targetRow.length) {
							$targetRow.addClass('wlgf-row--highlighted');
							setTimeout(function() {
								$targetRow.removeClass('wlgf-row--highlighted');
							}, 4000);
						}

						// Show Clone Success Confirmation Modal
						window.WLGF_ManageForms.showCloneSuccessModal(cloneData);
					}
				},
				error: function() {
					alert('Unable to clone form. Please refresh the page and try again.');
				},
				complete: function() {
					$btn.prop('disabled', false).html(originalHtml);
				}
			});
			return false;
		},

		showCloneSuccessModal: function(data) {
			if (!data) {
				return;
			}

			const cloneId = data.clone_id || '';
			const cloneName = data.clone_name || ('Form #' + cloneId);
			const shortcode = data.shortcode || ('[WLFG id="' + cloneId + '"]');
			const originalName = data.original_name || '';
			const editUrl = data.edit_url || ('admin.php?page=wlgf-form-generator&form-id=' + cloneId);

			$('#wlgf-clone-modal-id-badge').text('#' + cloneId);
			$('#wlgf-clone-modal-name').text(cloneName);
			$('#wlgf-clone-modal-shortcode').text(shortcode);

			if (originalName) {
				$('#wlgf-clone-modal-source').text('Duplicated from "' + originalName + '"');
			} else {
				$('#wlgf-clone-modal-source').text('New independent duplicate created');
			}

			$('#wlgf-clone-modal-edit-link').attr('href', editUrl);

			// Reset copy button state
			const $copyBtn = $('#wlgf-clone-modal-copy-btn');
			$copyBtn.removeClass('copied');
			$copyBtn.find('i').removeClass('fa-solid fa-check').addClass('fa-regular fa-copy');
			$('#wlgf-clone-modal-copy-text').text('Copy Shortcode');

			// Show modal
			$('#wlgf-clone-modal').addClass('wlgf-modal-overlay--active');
		},

		deleteForm: function(wlgf_form_id, triggerBtn) {
			const formName = $('tr#' + wlgf_form_id + ' td:nth-child(2)').text().trim();
			wlgf_pending_delete_id = wlgf_form_id;

			$('#wlgf-delete-modal-msg').html('Are you sure you want to delete form <strong>"' + $('<div>').text(formName).html() + '"</strong> (ID: #' + wlgf_form_id + ')?');
			$('#wlgf-delete-modal').addClass('wlgf-modal-overlay--active');
			return false;
		}
	};

	// Global entry point bridge for inline onclick compatibility
	window.WLGF = function(wlgf_form_id, do_action, triggerBtn) {
		if (do_action === 'copy') {
			return window.WLGF_ManageForms.copy(wlgf_form_id);
		} else if (do_action === 'clone') {
			return window.WLGF_ManageForms.clone(wlgf_form_id, triggerBtn);
		} else if (do_action === 'delete') {
			return window.WLGF_ManageForms.deleteForm(wlgf_form_id, triggerBtn);
		}
		return false;
	};

	$(document).ready(function() {
		window.WLGF_ManageForms.init();
	});

})(jQuery);