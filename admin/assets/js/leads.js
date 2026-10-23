/**
 * Lead Generation Form - Leads Management Script
 */

(function($) {
	'use strict';

	let wlgf_leads_datatable = null;
	let pendingDeleteAction = null;
	let currentNotesFormId = null;
	let currentNotesLeadIndex = null;
	let $activeNotesBtn = null;

	function renderNotesList(notes) {
		let html = '';
		if (!notes || notes.length === 0) {
			html = '<p class="wlgf-text-muted wlgf-m-0 wlgf-text-sm">No internal notes recorded yet for this lead.</p>';
		} else {
			notes.forEach(function(note) {
				html += '<div class="wlgf-note-item">';
				html += '<div class="wlgf-note-item__meta">';
				html += '<span><i class="fa-regular fa-user"></i> ' + $('<div>').text(note.user || 'Admin').html() + '</span>';
				html += '<span><i class="fa-regular fa-clock"></i> ' + $('<div>').text(note.time || '').html() + '</span>';
				html += '</div>';
				html += '<div class="wlgf-note-item__body">' + $('<div>').text(note.text || '').html() + '</div>';
				html += '</div>';
			});
		}
		$('#wlgf-lead-notes-list').html(html);
	}

	window.WLGF_Leads = {
		init: function() {
			this.bindEvents();
			this.initSearchableSelect();
		},

		initSearchableSelect: function() {
			const $container = $('#wlgf-form-searchable-select');
			if (!$container.length) return;

			const $trigger = $('#wlgf-form-search-trigger');
			const $dropdown = $('#wlgf-form-search-dropdown');
			const $searchInput = $('#wlgf-form-search-input');
			const $searchClear = $('#wlgf-form-search-clear');
			const $options = $('#wlgf-form-search-options .wlgf-searchable-select__option');
			const $empty = $('#wlgf-form-search-empty');
			const $label = $('#wlgf-form-search-current-label');
			const $nativeSelect = $('#wlgf-form-list');

			function openDropdown() {
				$container.addClass('is-open');
				$trigger.attr('aria-expanded', 'true');
				$dropdown.show();
				$searchInput.val('').trigger('input');
				setTimeout(function() {
					$searchInput.focus();
				}, 50);

				// Scroll selected into view if exists
				const $selected = $options.filter('.is-selected');
				if ($selected.length) {
					const optList = $('#wlgf-form-search-options')[0];
					if (optList && $selected[0]) {
						optList.scrollTop = $selected[0].offsetTop - optList.offsetTop - 10;
					}
				}
			}

			function closeDropdown() {
				$container.removeClass('is-open');
				$trigger.attr('aria-expanded', 'false');
				$dropdown.hide();
				$options.removeClass('is-highlighted');
			}

			// Toggle dropdown on trigger click
			$trigger.on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				if ($container.hasClass('is-open')) {
					closeDropdown();
				} else {
					openDropdown();
				}
			});

			// Close on click outside
			$(document).on('click', function(e) {
				if (!$(e.target).closest($container).length) {
					closeDropdown();
				}
			});

			// Filter options on typing
			$searchInput.on('input', function() {
				const query = $(this).val().toLowerCase().trim();
				if (query.length > 0) {
					$searchClear.show();
				} else {
					$searchClear.hide();
				}

				let visibleCount = 0;
				$options.each(function() {
					const $opt = $(this);
					const val = $opt.attr('data-value');
					if (val === '0') {
						if (query.length === 0) {
							$opt.show();
							visibleCount++;
						} else {
							$opt.hide();
						}
						return;
					}
					const searchStr = ($opt.attr('data-search') || $opt.text()).toLowerCase();
					if (searchStr.indexOf(query) !== -1) {
						$opt.show();
						visibleCount++;
					} else {
						$opt.hide();
					}
				});

				if (visibleCount === 0) {
					$empty.show();
				} else {
					$empty.hide();
				}
			});

			// Clear search button
			$searchClear.on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				$searchInput.val('').trigger('input').focus();
			});

			// Option selection
			$options.on('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				const $opt = $(this);
				const val = $opt.attr('data-value');
				const label = $opt.attr('data-label') || $opt.find('.wlgf-searchable-select__option-name').text().trim();

				$options.removeClass('is-selected');
				$opt.addClass('is-selected');

				$label.text(label);
				closeDropdown();

				// Update native select and trigger load
				$nativeSelect.val(val).trigger('change');
				if (typeof window.WLGF === 'function') {
					window.WLGF(val, 'load', '');
				}
			});

			// Keyboard navigation
			$trigger.on('keydown', function(e) {
				if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
					e.preventDefault();
					openDropdown();
				}
			});

			$searchInput.on('keydown', function(e) {
				const $visibleOpts = $options.filter(':visible');
				let $highlighted = $visibleOpts.filter('.is-highlighted');

				if (e.key === 'ArrowDown') {
					e.preventDefault();
					if (!$highlighted.length || $highlighted.index() === $visibleOpts.last().index()) {
						$visibleOpts.removeClass('is-highlighted');
						$visibleOpts.first().addClass('is-highlighted');
						if ($visibleOpts.first()[0]) {
							$visibleOpts.first()[0].scrollIntoView({ block: 'nearest' });
						}
					} else {
						const nextIdx = $visibleOpts.index($highlighted) + 1;
						$visibleOpts.removeClass('is-highlighted');
						$visibleOpts.eq(nextIdx).addClass('is-highlighted');
						if ($visibleOpts.eq(nextIdx)[0]) {
							$visibleOpts.eq(nextIdx)[0].scrollIntoView({ block: 'nearest' });
						}
					}
				} else if (e.key === 'ArrowUp') {
					e.preventDefault();
					if (!$highlighted.length || $highlighted.index() === $visibleOpts.first().index()) {
						$visibleOpts.removeClass('is-highlighted');
						$visibleOpts.last().addClass('is-highlighted');
						if ($visibleOpts.last()[0]) {
							$visibleOpts.last()[0].scrollIntoView({ block: 'nearest' });
						}
					} else {
						const prevIdx = $visibleOpts.index($highlighted) - 1;
						$visibleOpts.removeClass('is-highlighted');
						$visibleOpts.eq(prevIdx).addClass('is-highlighted');
						if ($visibleOpts.eq(prevIdx)[0]) {
							$visibleOpts.eq(prevIdx)[0].scrollIntoView({ block: 'nearest' });
						}
					}
				} else if (e.key === 'Enter') {
					e.preventDefault();
					if ($highlighted.length) {
						$highlighted.trigger('click');
					} else if ($visibleOpts.length === 1) {
						$visibleOpts.first().trigger('click');
					}
				} else if (e.key === 'Escape') {
					e.preventDefault();
					closeDropdown();
					$trigger.focus();
				}
			});

			// If native select is changed externally, update the searchable select label and active option
			$nativeSelect.on('change', function() {
				const currentVal = $(this).val();
				const $matchingOpt = $options.filter('[data-value="' + currentVal + '"]');
				if ($matchingOpt.length) {
					$options.removeClass('is-selected');
					$matchingOpt.addClass('is-selected');
					const label = $matchingOpt.attr('data-label') || $matchingOpt.find('.wlgf-searchable-select__option-name').text().trim();
					$label.text(label);
				}
			});
		},

		bindEvents: function() {
			// Modal Close / Cancel
			$(document).on('click', '#wlgf-modal-close-btn, #wlgf-modal-cancel-btn', function() {
				$('#wlgf-lead-delete-modal').removeClass('wlgf-modal-overlay--active').addClass('wlgf-d-none');
				pendingDeleteAction = null;
			});

			// Backdrop click to close modal
			$(document).on('click', '#wlgf-lead-delete-modal', function(e) {
				if (e.target === this) {
					$('#wlgf-lead-delete-modal').removeClass('wlgf-modal-overlay--active').addClass('wlgf-d-none');
					pendingDeleteAction = null;
				}
			});

			// Modal Confirm Delete
			$(document).on('click', '#wlgf-modal-confirm-delete', function() {
				if (typeof pendingDeleteAction === 'function') {
					pendingDeleteAction();
				}
				$('#wlgf-lead-delete-modal').removeClass('wlgf-modal-overlay--active').addClass('wlgf-d-none');
				pendingDeleteAction = null;
			});

			// Select All Checkbox
			$(document).on('change', '#wlgf-select-all', function() {
				$('input:checkbox[name=wlgf-lead-id]').prop('checked', this.checked);
			});

			// Sync Select All Checkbox when row checkboxes change
			$(document).on('change', 'input:checkbox[name=wlgf-lead-id]', function() {
				const total = $('input:checkbox[name=wlgf-lead-id]').length;
				const checked = $('input:checkbox[name=wlgf-lead-id]:checked').length;
				$('#wlgf-select-all').prop('checked', total > 0 && total === checked);
			});

			// Mini-CRM Status Update (PRO-011)
			$(document).on('change', '.wlgf-status-select', function() {
				const $select = $(this);
				const formId = $select.data('form-id');
				const leadIndex = $select.data('lead-index');
				const status = $select.val();

				$select.closest('tr').attr('data-status', status);

				$.ajax({
					type: 'POST',
					url: Leads.ajaxUrl,
					data: {
						action: 'wlgf_update_lead_status',
						form_id: formId,
						lead_index: leadIndex,
						status: status,
						nonce: Leads.nonce
					},
					success: function(res) {
						if (window.WLGF_Kanban && typeof window.WLGF_Kanban.renderBoard === 'function') {
							window.WLGF_Kanban.renderBoard();
						}
						if (typeof wlgfToast === 'function') {
							wlgfToast('Lead status updated successfully.', 'success');
						}
					}
				});
			});

			// Mini-CRM Internal Notes Modal Open
			$(document).on('click', '.wlgf-open-notes-btn', function(e) {
				e.preventDefault();
				$activeNotesBtn = $(this);
				currentNotesFormId = $activeNotesBtn.data('form-id');
				currentNotesLeadIndex = $activeNotesBtn.data('lead-index');
				let notesData = [];
				try {
					notesData = $activeNotesBtn.attr('data-notes') ? JSON.parse($activeNotesBtn.attr('data-notes')) : [];
				} catch (e) {
					notesData = [];
				}

				renderNotesList(notesData);
				$('#wlgf-new-note-text').val('');
				$('#wlgf-lead-notes-modal').addClass('wlgf-modal-overlay--active').removeClass('wlgf-d-none');
			});

			// Notes Modal Close
			$(document).on('click', '#wlgf-notes-modal-close-btn, #wlgf-notes-modal-cancel-btn', function() {
				$('#wlgf-lead-notes-modal').removeClass('wlgf-modal-overlay--active').addClass('wlgf-d-none');
			});

			// Add Note Save Button
			$(document).on('click', '#wlgf-add-note-btn', function() {
				const noteText = $('#wlgf-new-note-text').val().trim();
				if (!noteText) {
					$('#wlgf-new-note-text').focus();
					return;
				}

				const $saveBtn = $(this);
				$saveBtn.prop('disabled', true);

				$.ajax({
					type: 'POST',
					url: Leads.ajaxUrl,
					data: {
						action: 'wlgf_add_lead_note',
						form_id: currentNotesFormId,
						lead_index: currentNotesLeadIndex,
						note: noteText,
						nonce: Leads.nonce
					},
					success: function(response) {
						$saveBtn.prop('disabled', false);
						if (response && response.success && response.data) {
							renderNotesList(response.data.notes);
							$('#wlgf-new-note-text').val('');
							if ($activeNotesBtn) {
								$activeNotesBtn.attr('data-notes', JSON.stringify(response.data.notes));
								$activeNotesBtn.find('.wlgf-notes-count').text(response.data.count);
							}
						}
					},
					error: function() {
						$saveBtn.prop('disabled', false);
					}
				});
			});

			// Digital Signature Preview Lightbox Modal
			$(document).on('click', '.wlgf-signature-card', function(e) {
				e.preventDefault();
				const imgSrc = $(this).attr('data-signature') || $(this).find('img').attr('src');
				if (imgSrc) {
					$('#wlgf-signature-preview-img').attr('src', imgSrc);
					$('#wlgf-signature-download-btn').attr('href', imgSrc);
					$('#wlgf-signature-preview-modal').removeClass('wlgf-d-none').addClass('wlgf-modal-overlay--active');
				}
			});

			$(document).on('click', '#wlgf-signature-modal-close-btn, #wlgf-signature-modal-close-action', function() {
				$('#wlgf-signature-preview-modal').removeClass('wlgf-modal-overlay--active').addClass('wlgf-d-none');
			});

			$(document).on('click', '#wlgf-signature-preview-modal', function(e) {
				if (e.target === this) {
					$('#wlgf-signature-preview-modal').removeClass('wlgf-modal-overlay--active').addClass('wlgf-d-none');
				}
			});

			// Media / File Upload Preview Lightbox Modal
			function closeMediaLightbox() {
				const $modal = $('#wlgf-media-lightbox-modal');
				if ($modal.length && !$modal.hasClass('wlgf-d-none')) {
					const $video = $modal.find('video');
					if ($video.length && $video[0].pause) {
						$video[0].pause();
					}
					const $audio = $modal.find('audio');
					if ($audio.length && $audio[0].pause) {
						$audio[0].pause();
					}
					$modal.find('#wlgf-lightbox-body').empty();
					$modal.removeClass('wlgf-modal-overlay--active').addClass('wlgf-d-none');
				}
			}

			function openMediaLightbox(fileUrl, fileName, previewType) {
				if (!fileUrl) return;

				if (!fileName) {
					try {
						const u = new URL(fileUrl, window.location.origin);
						const parts = u.pathname.split('/');
						fileName = parts[parts.length - 1] || 'attachment';
					} catch (e) {
						fileName = 'attachment';
					}
				}

				const ext = (fileName.indexOf('.') !== -1) ? fileName.split('.').pop().toLowerCase() : '';
				if (!previewType) {
					if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'avif'].indexOf(ext) !== -1) {
						previewType = 'image';
					} else if (['mp4', 'webm', 'ogg', 'mov', 'm4v'].indexOf(ext) !== -1) {
						previewType = 'video';
					} else if (['mp3', 'wav', 'm4a'].indexOf(ext) !== -1) {
						previewType = 'audio';
					} else if (ext === 'pdf') {
						previewType = 'pdf';
					} else {
						previewType = 'file';
					}
				}

				const $modal = $('#wlgf-media-lightbox-modal');
				const $body = $('#wlgf-lightbox-body');
				const $icon = $('#wlgf-lightbox-file-icon');
				const $title = $('#wlgf-lightbox-title');
				const $filename = $('#wlgf-lightbox-filename');
				const $extBadge = $('#wlgf-lightbox-ext-badge');
				const $downloadBtn = $('#wlgf-lightbox-download-btn');
				const $externalBtn = $('#wlgf-lightbox-external-btn');

				$filename.text(fileName).attr('title', fileName);
				$extBadge.text(ext ? ext.toUpperCase() : 'FILE');
				$downloadBtn.attr('href', fileUrl).attr('download', fileName);
				$externalBtn.attr('href', fileUrl);

				$body.empty();

				if (previewType === 'image') {
					$icon.attr('class', 'fa-solid fa-image wlgf-text-primary');
					$title.text('Image Preview');
					const $img = $('<img>', {
						src: fileUrl,
						class: 'wlgf-lightbox-img',
						alt: fileName
					});
					const $viewport = $('<div class="wlgf-lightbox-media-viewport"></div>').append($img);
					$body.append($viewport);
				} else if (previewType === 'video') {
					$icon.attr('class', 'fa-solid fa-video wlgf-text-primary');
					$title.text('Screen Recording / Video Preview');
					const $video = $('<video>', {
						src: fileUrl,
						class: 'wlgf-lightbox-video',
						controls: true,
						autoplay: true,
						playsinline: true
					});
					const $viewport = $('<div class="wlgf-lightbox-media-viewport"></div>').append($video);
					$body.append($viewport);
				} else if (previewType === 'audio') {
					$icon.attr('class', 'fa-solid fa-file-audio wlgf-text-primary');
					$title.text('Audio Preview');
					const $audio = $('<audio>', {
						src: fileUrl,
						class: 'wlgf-lightbox-audio',
						controls: true,
						autoplay: true
					});
					const $viewport = $('<div class="wlgf-lightbox-audio-viewport"><div class="wlgf-lightbox-audio-icon"><i class="fa-solid fa-volume-high"></i></div></div>').append($audio);
					$body.append($viewport);
				} else if (previewType === 'pdf') {
					$icon.attr('class', 'fa-solid fa-file-pdf wlgf-text-danger');
					$title.text('PDF Document Preview');
					const $iframe = $('<iframe>', {
						src: fileUrl,
						class: 'wlgf-lightbox-pdf-frame',
						title: fileName
					});
					const $viewport = $('<div class="wlgf-lightbox-pdf-viewport"></div>').append($iframe);
					$body.append($viewport);
				} else {
					$icon.attr('class', 'fa-solid fa-file-lines wlgf-text-primary');
					$title.text('File Attachment');
					const docHtml =
						'<div class="wlgf-lightbox-doc-viewport">' +
							'<div class="wlgf-lightbox-doc-card">' +
								'<div class="wlgf-lightbox-doc-icon"><i class="fa-solid fa-file-lines"></i></div>' +
								'<h4 class="wlgf-lightbox-doc-name">' + $('<div>').text(fileName).html() + '</h4>' +
								'<p class="wlgf-lightbox-doc-desc">This file format cannot be rendered inline directly. You can download or open it in a new browser tab.</p>' +
								'<a href="' + fileUrl + '" download="' + $('<div>').text(fileName).html() + '" class="wlgf-btn wlgf-btn--primary wlgf-btn--sm">' +
									'<i class="fa-solid fa-download"></i> Download File' +
								'</a>' +
							'</div>' +
						'</div>';
					$body.html(docHtml);
				}

				$modal.removeClass('wlgf-d-none').addClass('wlgf-modal-overlay--active');
			}

			$(document).on('click', '.wlgf-lightbox-trigger, .wlgf-lead-img-thumb, .wlgf-file-thumb-link', function(e) {
				e.preventDefault();
				e.stopPropagation();
				const $el = $(this);
				let fileUrl = $el.attr('data-file-url');
				if (!fileUrl) {
					if ($el.is('img')) {
						fileUrl = $el.attr('src');
					} else {
						fileUrl = $el.attr('href') || $el.find('img').attr('src');
					}
				}
				let fileName = $el.attr('data-file-name') || $el.find('img').attr('alt') || '';
				let previewType = $el.attr('data-preview-type') || '';
				openMediaLightbox(fileUrl, fileName, previewType);
			});

			$(document).on('click', '#wlgf-lightbox-modal-close-btn, #wlgf-lightbox-modal-close-action', function() {
				closeMediaLightbox();
			});

			$(document).on('click', '#wlgf-media-lightbox-modal', function(e) {
				if (e.target === this) {
					closeMediaLightbox();
				}
			});

			// Single Lead Details Preview Modal (PRO-012) & Next/Prev Navigation
			let currentModalFormId = null;
			let currentModalLeadIndex = null;

			function openLeadDetailsModal(formId, leadIndex) {
				if (!formId || leadIndex === undefined || leadIndex === null || leadIndex === '') {
					return;
				}

				currentModalFormId = formId;
				currentModalLeadIndex = leadIndex;

				const $modal = $('#wlgf-lead-details-modal');
				const $body = $('#wlgf-lead-details-modal-body');
				$modal.removeClass('wlgf-d-none').addClass('wlgf-modal-overlay--active');
				$body.html(
					'<div style="text-align: center; padding: 50px 20px;">' +
						'<i class="fa-solid fa-spinner fa-spin" style="font-size: 32px; color: var(--wlgf-primary, #2563eb);"></i>' +
						'<p style="margin-top: 14px; font-size: 14px; color: #64748b; font-weight: 500;">' + (typeof Leads !== 'undefined' && Leads.loadingTxt ? Leads.loadingTxt : 'Loading lead details...') + '</p>' +
					'</div>'
				);

				// Disable nav buttons while loading
				$('#wlgf-lead-prev-btn, #wlgf-lead-next-btn').prop('disabled', true);

				$.ajax({
					type: 'POST',
					url: Leads.ajaxUrl,
					data: {
						action: 'wlgf_get_lead_details',
						form_id: formId,
						lead_index: leadIndex,
						nonce: Leads.nonce
					},
					success: function(response) {
						if (response && response.success && response.data) {
							$body.html(response.data.html);
							if (response.data.pdf_url) {
								var cleanPdfUrl = String(response.data.pdf_url).replace(/&amp;/g, '&');
								$('#wlgf-details-pdf-btn').attr('href', cleanPdfUrl).show();
							} else {
								$('#wlgf-details-pdf-btn').hide();
							}
							if (response.data.client_name) {
								let titleText = 'Lead Details: ' + response.data.client_name;
								if (response.data.current_lead_num && response.data.total_leads) {
									titleText += ' (' + response.data.current_lead_num + ' of ' + response.data.total_leads + ')';
								}
								$('#wlgf-lead-details-modal-title').text(titleText);
							}

							// Setup Previous Lead Button
							if (response.data.prev_lead_index !== null && response.data.prev_lead_index !== undefined) {
								$('#wlgf-lead-prev-btn')
									.prop('disabled', false)
									.data('target-index', response.data.prev_lead_index)
									.attr('title', 'Previous Lead');
							} else {
								$('#wlgf-lead-prev-btn')
									.prop('disabled', true)
									.data('target-index', null)
									.attr('title', 'No previous lead');
							}

							// Setup Next Lead Button
							if (response.data.next_lead_index !== null && response.data.next_lead_index !== undefined) {
								$('#wlgf-lead-next-btn')
									.prop('disabled', false)
									.data('target-index', response.data.next_lead_index)
									.attr('title', 'Next Lead');
							} else {
								$('#wlgf-lead-next-btn')
									.prop('disabled', true)
									.data('target-index', null)
									.attr('title', 'No next lead');
							}
						} else {
							$body.html(
								'<div class="wlgf-alert wlgf-alert--danger" style="margin: 20px;">' +
									'<i class="fa-solid fa-triangle-exclamation"></i> ' +
									((response && response.data && response.data.message) ? response.data.message : 'Unable to load lead details.') +
								'</div>'
							);
							$('#wlgf-lead-prev-btn, #wlgf-lead-next-btn').prop('disabled', true);
						}
					},
					error: function() {
						$body.html(
							'<div class="wlgf-alert wlgf-alert--danger" style="margin: 20px;">' +
								'<i class="fa-solid fa-triangle-exclamation"></i> Error connecting to server to load lead details.' +
							'</div>'
						);
						$('#wlgf-lead-prev-btn, #wlgf-lead-next-btn').prop('disabled', true);
					}
				});
			}

			$(document).on('click', '.wlgf-view-lead-btn', function(e) {
				e.preventDefault();
				const formId = $(this).data('form-id') || $('#wlgf-form-list').val();
				const leadIndex = $(this).data('lead-index');
				openLeadDetailsModal(formId, leadIndex);
			});

			// Navigate Previous Lead
			$(document).on('click', '#wlgf-lead-prev-btn', function(e) {
				e.preventDefault();
				if ($(this).prop('disabled')) return;
				const targetIdx = $(this).data('target-index');
				if (targetIdx !== null && targetIdx !== undefined && currentModalFormId) {
					openLeadDetailsModal(currentModalFormId, targetIdx);
				}
			});

			// Navigate Next Lead
			$(document).on('click', '#wlgf-lead-next-btn', function(e) {
				e.preventDefault();
				if ($(this).prop('disabled')) return;
				const targetIdx = $(this).data('target-index');
				if (targetIdx !== null && targetIdx !== undefined && currentModalFormId) {
					openLeadDetailsModal(currentModalFormId, targetIdx);
				}
			});

			// Keyboard Navigation (Left / Right Arrows) & Escape to close
			$(document).on('keydown', function(e) {
				if ($('#wlgf-media-lightbox-modal').hasClass('wlgf-modal-overlay--active')) {
					if (e.key === 'Escape' || e.keyCode === 27) {
						closeMediaLightbox();
						return;
					}
				}
				if ($('#wlgf-lead-details-modal').hasClass('wlgf-modal-overlay--active')) {
					if ($(e.target).is('input, textarea, select')) return;
					if (e.key === 'ArrowLeft' && !$('#wlgf-lead-prev-btn').prop('disabled')) {
						$('#wlgf-lead-prev-btn').trigger('click');
					} else if (e.key === 'ArrowRight' && !$('#wlgf-lead-next-btn').prop('disabled')) {
						$('#wlgf-lead-next-btn').trigger('click');
					}
				}
			});

			// Close Lead Details Modal
			$(document).on('click', '#wlgf-lead-details-modal-close-btn, #wlgf-lead-details-modal-close-action', function() {
				$('#wlgf-lead-details-modal').removeClass('wlgf-modal-overlay--active').addClass('wlgf-d-none');
			});

			$(document).on('click', '#wlgf-lead-details-modal', function(e) {
				if (e.target === this) {
					$('#wlgf-lead-details-modal').removeClass('wlgf-modal-overlay--active').addClass('wlgf-d-none');
				}
			});

			// Status changer inside Lead Details Modal
			$(document).on('change', '#wlgf-detail-status-select', function() {
				const $select = $(this);
				const formId = $select.data('form-id');
				const leadIndex = $select.data('lead-index');
				const status = $select.val();

				// Update badge in modal
				const $badge = $('#wlgf-detail-current-status-badge');
				$badge.attr('class', 'wlgf-badge wlgf-badge--' + status + ' wlgf-badge--lg').text(status.toUpperCase());

				// Sync with table row and select
				const $tableRow = $('tr[data-lead-index="' + leadIndex + '"], #' + leadIndex);
				if ($tableRow.length) {
					$tableRow.attr('data-status', status);
					$tableRow.find('.wlgf-status-select, .wlgf-lead-status-select').val(status);
				}

				$.ajax({
					type: 'POST',
					url: Leads.ajaxUrl,
					data: {
						action: 'wlgf_update_lead_status',
						form_id: formId,
						lead_index: leadIndex,
						status: status,
						nonce: Leads.nonce
					},
					success: function(response) {
						if (typeof WLGF_Kanban !== 'undefined' && WLGF_Kanban.activeView === 'kanban') {
							WLGF_Kanban.init();
						}
					}
				});
			});

			// Add quick note inside Lead Details Modal
			$(document).on('click', '#wlgf-detail-quick-note-btn', function() {
				const $btn = $(this);
				const formId = $btn.data('form-id');
				const leadIndex = $btn.data('lead-index');
				const noteText = $('#wlgf-detail-quick-note-input').val().trim();

				if (!noteText) {
					$('#wlgf-detail-quick-note-input').focus();
					return;
				}

				$btn.prop('disabled', true);

				$.ajax({
					type: 'POST',
					url: Leads.ajaxUrl,
					data: {
						action: 'wlgf_add_lead_note',
						form_id: formId,
						lead_index: leadIndex,
						note: noteText,
						nonce: Leads.nonce
					},
					success: function(response) {
						$btn.prop('disabled', false);
						if (response && response.success && response.data) {
							$('#wlgf-detail-quick-note-input').val('');
							let html = '';
							response.data.notes.forEach(function(note) {
								html += '<div class="wlgf-note-item">';
								html += '<div class="wlgf-note-item__meta">';
								html += '<span><i class="fa-regular fa-user"></i> ' + $('<div>').text(note.user || 'Admin').html() + '</span>';
								html += '<span><i class="fa-regular fa-clock"></i> ' + $('<div>').text(note.time || '').html() + '</span>';
								html += '</div>';
								html += '<div class="wlgf-note-item__body">' + $('<div>').text(note.text || '').html() + '</div>';
								html += '</div>';
							});
							$('#wlgf-detail-modal-notes-list').html(html);

							// Update table button count
							const $rowNotesBtn = $('button.wlgf-open-notes-btn[data-lead-index="' + leadIndex + '"]');
							if ($rowNotesBtn.length) {
								$rowNotesBtn.attr('data-notes', JSON.stringify(response.data.notes));
								$rowNotesBtn.find('.wlgf-notes-count').text(response.data.count);
							}
						}
					},
					error: function() {
						$btn.prop('disabled', false);
					}
				});
			});

			// Print Lead Details
			$(document).on('click', '#wlgf-print-lead-btn', function() {
				const content = $('#wlgf-lead-details-modal-body').html();
				const printWin = window.open('', '_blank', 'width=840,height=700');
				printWin.document.write('<!DOCTYPE html><html><head><title>' + document.title + '</title>');
				printWin.document.write('<style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;padding:24px;color:#1e293b;} table{width:100%;border-collapse:collapse;} td{padding:10px 8px;border-bottom:1px solid #e2e8f0;} .wlgf-btn, .wlgf-detail-add-note, #wlgf-detail-status-select{display:none;} .wlgf-badge{display:inline-block;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:bold;background:#e2e8f0;}</style>');
				printWin.document.write('</head><body>');
				printWin.document.write(content);
				printWin.document.write('</body></html>');
				printWin.document.close();
				printWin.focus();
				setTimeout(function() {
					printWin.print();
					printWin.close();
				}, 400);
			});

			// Newsletter Subscribers vs All Leads filter toggle (PRO-008)
			$(document).on('click', '.wlgf-filter-leads-btn', function(e) {
				e.preventDefault();
				const formId = $(this).data('form-id');
				const newsletterOnly = $(this).data('newsletter-only');
				window.WLGF_Leads.load(formId, newsletterOnly);
			});
		},

		load: function(wlgf_form_id, newsletter_only) {
			if (!wlgf_form_id || wlgf_form_id === '0') {
				$('#wlgf-loader-process').addClass('wlgf-d-none d-none');
				$('#wlgf-table-container').html(
					'<div class="wlgf-empty-state">' +
						'<div class="wlgf-empty-state__icon"><i class="fa-solid fa-users-viewfinder"></i></div>' +
						'<h3 class="wlgf-empty-state__title">No Form Selected</h3>' +
						'<p class="wlgf-empty-state__desc">Select a form from the dropdown selector above to inspect, search, and export its captured lead inquiries.</p>' +
					'</div>'
				);
				return false;
			}

			$('#wlgf-table-container').empty();
			$('#wlgf-loader-process').removeClass('wlgf-d-none d-none');

			$.ajax({
				type: 'POST',
				url: Leads.ajaxUrl,
				data: {
					action: 'wlgf_lead_loader',
					do: 'load',
					wlgf_form_id: wlgf_form_id,
					newsletter_only: (newsletter_only === 1 || newsletter_only === '1') ? '1' : '0',
					nonce: Leads.nonce
				},
				success: function(result) {
					$('#wlgf-loader-process').addClass('wlgf-d-none d-none');
					$('#wlgf-table-container').html(result);

					if ($('#forms-tables').length) {
						if ($.fn.dataTable) {
							$.fn.dataTable.ext.errMode = 'console';
						}

						let dateTimeColIdx = $('#forms-tables thead th.wlgf-th-datetime').index();
						if (dateTimeColIdx === -1) {
							$('#forms-tables thead th').each(function(idx) {
								const txt = $(this).text().trim().toLowerCase();
								if (txt.includes('date') || txt.includes('time')) {
									dateTimeColIdx = idx;
									return false;
								}
							});
						}
						const defaultOrder = (dateTimeColIdx >= 0) ? [[dateTimeColIdx, 'desc']] : [];

						wlgf_leads_datatable = $('#forms-tables').DataTable({
							responsive: true,
							order: defaultOrder,
							columnDefs: [
								{ orderable: false, targets: [-1, -2, -3] },
								{ defaultContent: '—', targets: '_all' }
							],
							language: {
								search: '_INPUT_',
								searchPlaceholder: 'Search leads...'
							}
						});

						if (window.WLGF_Kanban) {
							window.WLGF_Kanban.renderBoard();
						}
					}
				},
				error: function() {
					$('#wlgf-loader-process').addClass('wlgf-d-none d-none');
				}
			});
		},

		deleteSingle: function(wlgf_form_id, wlgf_row_id) {
			const doDelete = function() {
				$.ajax({
					type: 'POST',
					url: Leads.ajaxUrl,
					data: {
						action: 'wlgf_lead_loader',
						do: 'delete',
						wlgf_form_id: wlgf_form_id,
						row_id: wlgf_row_id,
						wlgf_row_id: wlgf_row_id,
						nonce: Leads.nonce
					},
					success: function() {
						const rowEl = document.getElementById(String(wlgf_row_id));
						if (wlgf_leads_datatable && rowEl) {
							wlgf_leads_datatable.row(rowEl).remove().draw(false);
						} else if (rowEl) {
							$(rowEl).fadeOut(300, function() {
								$(this).remove();
							});
						}
						if (window.WLGF_Kanban && typeof window.WLGF_Kanban.renderBoard === 'function') {
							window.WLGF_Kanban.renderBoard();
						}
						if (typeof wlgfToast === 'function') {
							wlgfToast('Lead permanently deleted.', 'success');
						}
					}
				});
			};

			if (typeof wlgfConfirm === 'function') {
				wlgfConfirm({
					title: 'Confirm Lead Deletion',
					message: 'Are you sure you want to permanently delete this lead record? This action cannot be undone.',
					type: 'danger',
					confirmText: 'Yes, Delete Permanently',
					cancelText: 'Cancel'
				}).then(function(confirmed) {
					if (confirmed) {
						doDelete();
					}
				});
			} else {
				$('#wlgf-modal-message').text('Are you sure you want to permanently delete this lead record? This action cannot be undone.');
				$('#wlgf-lead-delete-modal').addClass('wlgf-modal-overlay--active').removeClass('wlgf-d-none');
				pendingDeleteAction = doDelete;
			}
		},

		deleteMultiple: function(wlgf_form_id) {
			const selectedIds = [];
			$('input:checkbox[name=wlgf-lead-id]:checked').each(function() {
				selectedIds.push($(this).val());
			});

			if (selectedIds.length === 0) {
				if (typeof wlgfAlert === 'function') {
					wlgfAlert({
						title: 'No Leads Selected',
						message: 'Please select at least one lead from the table to delete.',
						type: 'warning'
					});
				} else {
					alert('Please select at least one lead to delete.');
				}
				return false;
			}

			const doDeleteMultiple = function() {
				$.ajax({
					type: 'POST',
					url: Leads.ajaxUrl,
					data: {
						action: 'wlgf_lead_loader',
						do: 'multiple',
						wlgf_form_id: wlgf_form_id,
						row_id: selectedIds,
						wlgf_row_id: selectedIds,
						nonce: Leads.nonce
					},
					success: function() {
						selectedIds.forEach(function(rowId) {
							const rowEl = document.getElementById(String(rowId));
							if (wlgf_leads_datatable && rowEl) {
								wlgf_leads_datatable.row(rowEl).remove();
							} else if (rowEl) {
								$(rowEl).remove();
							}
						});
						if (wlgf_leads_datatable) {
							wlgf_leads_datatable.draw(false);
						}
						$('#wlgf-select-all').prop('checked', false);
						if (window.WLGF_Kanban && typeof window.WLGF_Kanban.renderBoard === 'function') {
							window.WLGF_Kanban.renderBoard();
						}
						if (typeof wlgfToast === 'function') {
							wlgfToast(selectedIds.length + ' lead records permanently deleted.', 'success');
						}
					}
				});
			};

			if (typeof wlgfConfirm === 'function') {
				wlgfConfirm({
					title: 'Confirm Bulk Deletion',
					message: 'Are you sure you want to permanently delete ' + selectedIds.length + ' selected lead records? This action cannot be undone.',
					type: 'danger',
					confirmText: 'Yes, Delete Permanently',
					cancelText: 'Cancel'
				}).then(function(confirmed) {
					if (confirmed) {
						doDeleteMultiple();
					}
				});
			} else {
				$('#wlgf-modal-message').text('Are you sure you want to permanently delete ' + selectedIds.length + ' selected lead records? This action cannot be undone.');
				$('#wlgf-lead-delete-modal').addClass('wlgf-modal-overlay--active').removeClass('wlgf-d-none');
				pendingDeleteAction = doDeleteMultiple;
			}
		}
	};

	// Global entry point bridge for inline onchange / onclick compatibility
	window.WLGF = function(wlgf_form_id, do_action, wlgf_row_id) {
		if (do_action === 'load') {
			window.WLGF_Leads.load(wlgf_form_id);
		} else if (do_action === 'delete') {
			window.WLGF_Leads.deleteSingle(wlgf_form_id, wlgf_row_id);
		} else if (do_action === 'multiple') {
			window.WLGF_Leads.deleteMultiple(wlgf_form_id);
		}
		return false;
	};

	$(document).ready(function() {
		window.WLGF_Leads.init();

		const initialFormId = $('#wlgf-form-list').val();
		if (initialFormId && initialFormId !== '0') {
			window.WLGF_Leads.load(initialFormId);
		}
	});

})(jQuery);