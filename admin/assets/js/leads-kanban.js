/**
 * Lead Generation Form Pro - Mini-CRM Kanban Pipeline Board Script
 * Version: 3.4.8
 */

(function($) {
	'use strict';

	window.WLGF_Kanban = {
		activeView: 'table', // 'table' or 'kanban'
		currentFormId: null,

		init: function() {
			// Restore saved view preference if available
			try {
				const savedView = localStorage.getItem('wlgf_lead_view');
				if (savedView === 'kanban') {
					this.switchToView('kanban', false);
				}
			} catch (e) {}

			// View switcher button click
			$(document).on('click', '.wlgf-view-btn', function() {
				const target = $(this).data('view');
				WLGF_Kanban.switchToView(target, true);
			});

			WLGF_Kanban.setupDragAndDrop();
		},

		switchToView: function(target, persist) {
			$('.wlgf-view-btn').removeClass('active');
			$(`.wlgf-view-btn[data-view="${target}"]`).addClass('active');

			if (persist) {
				try {
					localStorage.setItem('wlgf_lead_view', target);
				} catch (e) {}
			}

			if (target === 'kanban') {
				this.activeView = 'kanban';
				$('#wlgf-table-container').hide();
				$('#wlgf-kanban-container').show();

				// If form is selected but table not loaded yet, trigger load
				const formVal = $('#wlgf-form-list').val();
				if (formVal && formVal !== '0' && !$('#forms-tables, #wlgf-leads-table').length) {
					if (window.WLGF_Leads && typeof window.WLGF_Leads.load === 'function') {
						window.WLGF_Leads.load(formVal);
					}
				} else {
					WLGF_Kanban.renderBoard();
				}
			} else {
				this.activeView = 'table';
				$('#wlgf-kanban-container').hide();
				$('#wlgf-table-container').show();
			}
		},

		renderBoard: function() {
			const $container = $('#wlgf-kanban-container');
			if (!$container.length) return;

			const $empty = $('#wlgf-kanban-empty');
			const $boardWrap = $('#wlgf-kanban-board-wrap');
			const $table = $('#forms-tables, #wlgf-leads-table');

			// If no table or no rows or empty table
			if (!$table.length || $table.find('tbody tr').length === 0 || $table.find('tbody tr .dataTables_empty').length) {
				$boardWrap.hide();
				$empty.show();
				return;
			}

			// Leads are present, show board wrap and hide empty state
			$empty.hide();
			$boardWrap.show();

			// Clear columns
			$('.wlgf-kanban-cards').empty();
			$('.wlgf-kanban-col-badge').text('0');

			// Read rows from table
			let counts = {
				'new': 0,
				'contacted': 0,
				'followup': 0,
				'qualified': 0,
				'converted': 0,
				'abandoned': 0
			};

			const currentFormId = $('#wlgf-form-list').val();
			const ajaxUrl = (typeof Leads !== 'undefined' && Leads.ajaxUrl) ? Leads.ajaxUrl : 'admin-ajax.php';
			const ajaxNonce = (typeof Leads !== 'undefined' && Leads.nonce) ? Leads.nonce : '';

			// Determine column positions from table header
			const colHeaders = [];
			$table.find('thead th').each(function() {
				colHeaders.push($(this).text().trim().toLowerCase());
			});

			let nameColIdx = -1;
			let emailColIdx = -1;
			let phoneColIdx = -1;
			let optColIdx = -1;
			let subColIdx = -1;
			let dateColIdx = -1;

			colHeaders.forEach(function(h, cIdx) {
				if ((h.includes('name') || h === 'full name' || h === 'client' || h === 'customer' || h === 'member') && !h.includes('file') && !h.includes('user') && nameColIdx === -1) {
					nameColIdx = cIdx;
				} else if (h.includes('email') && emailColIdx === -1) {
					emailColIdx = cIdx;
				} else if ((h.includes('phone') || h.includes('tel') || h.includes('mobile')) && phoneColIdx === -1) {
					phoneColIdx = cIdx;
				} else if ((h.includes('property') || h.includes('type') || h.includes('tier') || h.includes('plan') || h.includes('option') || h.includes('service') || h.includes('category')) && optColIdx === -1 && !h.includes('lead status') && !h.includes('payment status')) {
					optColIdx = cIdx;
				} else if ((h.includes('address') || h.includes('location') || h.includes('square') || h.includes('company') || h.includes('subject')) && subColIdx === -1) {
					subColIdx = cIdx;
				} else if (h.includes('date') && dateColIdx === -1) {
					dateColIdx = cIdx;
				}
			});

			$table.find('tbody tr').each(function(idx) {
				const $tr = $(this);
				if ($tr.find('.dataTables_empty').length) return;

				const rowLeadIndex = $tr.attr('data-lead-index');
				const leadIndex = (rowLeadIndex !== undefined && rowLeadIndex !== '') ? rowLeadIndex : ($tr.data('lead-index') !== undefined ? $tr.data('lead-index') : idx);
				
				let rawStatus = $tr.find('.wlgf-status-select, .wlgf-lead-status-select').val() || $tr.attr('data-status') || 'new';
				rawStatus = String(rawStatus).toLowerCase();
				if (rawStatus === 'won') rawStatus = 'converted';
				else if (rawStatus === 'lost') rawStatus = 'abandoned';
				else if (rawStatus === 'review') rawStatus = 'followup';

				const status = (rawStatus in counts) ? rawStatus : 'new';

				let name = '';
				let email = '';
				let phone = '';
				let optName = '';
				let subText = '';
				let leadDate = '';

				if (nameColIdx !== -1) name = $tr.find('td').eq(nameColIdx).text().trim();
				if (emailColIdx !== -1) email = $tr.find('td').eq(emailColIdx).text().trim();
				if (phoneColIdx !== -1) phone = $tr.find('td').eq(phoneColIdx).text().trim();
				if (optColIdx !== -1) optName = $tr.find('td').eq(optColIdx).text().trim();
				if (subColIdx !== -1) subText = $tr.find('td').eq(subColIdx).text().trim();
				if (dateColIdx !== -1) leadDate = $tr.find('td').eq(dateColIdx).text().trim();

				// Fallbacks if not resolved by headers
				$tr.find('td').each(function(tdIdx) {
					const txt = $(this).text().trim();
					if (!txt || txt === '—') return;
					if (txt.indexOf('@') > 0 && !email) {
						email = txt;
					} else if (txt.match(/^[+0-9\s\-()]{7,20}$/) && !phone) {
						phone = txt;
					}
				});

				// If optName is empty or equals '—', fallback to col 0 if non-empty
				if (!optName || optName === '—') {
					const firstCol = $tr.find('td').eq(0).text().trim();
					if (firstCol && firstCol !== '—' && firstCol !== email && firstCol !== phone) {
						optName = firstCol;
					}
				}

				if (!name || name === '—') {
					name = 'Lead #' + (idx + 1);
				}

				const displayHeaderTag = optName ? optName : '#' + (idx + 1);
				const displayHeaderSub = subText ? subText : (leadDate ? leadDate : '');

				let pdfUrl = $tr.find('a[href*="wlgf_download_lead_pdf"]').prop('href') || $tr.find('a[href*="wlgf_download_lead_pdf"]').attr('href');
				if (!pdfUrl) {
					pdfUrl = `${ajaxUrl}?action=wlgf_download_lead_pdf&form_id=${currentFormId}&lead_index=${leadIndex}&_wpnonce=${ajaxNonce}&nonce=${ajaxNonce}`;
				} else {
					pdfUrl = String(pdfUrl).replace(/&amp;/g, '&');
				}

				const cardHtml = `
					<div class="wlgf-kanban-card" draggable="true" data-lead-index="${leadIndex}" data-lead-id="${leadIndex}">
						<div class="wlgf-card-header">
							<span class="wlgf-card-id" title="${displayHeaderTag}">${displayHeaderTag}</span>
							<span class="wlgf-card-date" title="${displayHeaderSub}">${displayHeaderSub}</span>
						</div>
						<h4 class="wlgf-card-name">${name}</h4>
						<div class="wlgf-card-meta">
							${email ? `<div class="wlgf-card-meta-item" title="${email}"><i class="fa-solid fa-envelope"></i> ${email}</div>` : ''}
							${phone ? `<div class="wlgf-card-meta-item"><i class="fa-solid fa-phone"></i> ${phone}</div>` : ''}
						</div>
						<div class="wlgf-card-actions">
							<div class="wlgf-card-btn-group">
								<button type="button" class="wlgf-card-action-btn wlgf-act-view wlgf-view-lead-btn" data-form-id="${currentFormId}" data-lead-index="${leadIndex}" title="View Lead Details"><i class="fa-solid fa-eye"></i></button>
								${phone ? `<a href="https://wa.me/${phone.replace(/[^0-9]/g, '')}" target="_blank" class="wlgf-card-action-btn wlgf-act-wa" title="WhatsApp Chat"><i class="fa-brands fa-whatsapp"></i></a>` : ''}
								${phone ? `<a href="tel:${phone}" class="wlgf-card-action-btn wlgf-act-call" title="Direct Phone Call"><i class="fa-solid fa-phone"></i></a>` : ''}
								<a href="${pdfUrl}" target="_blank" class="wlgf-card-action-btn wlgf-act-pdf" title="Download PDF"><i class="fa-solid fa-file-pdf"></i></a>
							</div>
							<span class="wlgf-badge wlgf-badge--${status}">${status.toUpperCase()}</span>
						</div>
					</div>
				`;

				counts[status]++;
				$(`#wlgf-cards-${status}`).append(cardHtml);
			});

			// Update counts
			for (let k in counts) {
				$(`#wlgf-count-${k}`).text(counts[k]);
			}
		},

		setupDragAndDrop: function() {
			let draggedCard = null;

			$(document).on('dragstart', '.wlgf-kanban-card', function(e) {
				draggedCard = this;
				$(this).addClass('wlgf-dragging');
				e.originalEvent.dataTransfer.setData('text/plain', $(this).data('lead-index'));
			});

			$(document).on('dragend', '.wlgf-kanban-card', function() {
				$(this).removeClass('wlgf-dragging');
				$('.wlgf-kanban-cards').removeClass('wlgf-drag-over');
			});

			$(document).on('dragover', '.wlgf-kanban-cards', function(e) {
				e.preventDefault();
				$(this).addClass('wlgf-drag-over');
			});

			$(document).on('dragleave', '.wlgf-kanban-cards', function() {
				$(this).removeClass('wlgf-drag-over');
			});

			$(document).on('drop', '.wlgf-kanban-cards', function(e) {
				e.preventDefault();
				$(this).removeClass('wlgf-drag-over');
				if (!draggedCard) return;

				const targetCol = $(this).closest('.wlgf-kanban-column');
				const newStatus = targetCol.data('status');
				const leadIndex = $(draggedCard).data('lead-index');
				const leadId = $(draggedCard).data('lead-id');
				const formId = $('#wlgf-form-list').val();

				$(this).append(draggedCard);

				// Update badge on card
				$(draggedCard).find('.wlgf-badge')
					.attr('class', 'wlgf-badge wlgf-badge--' + newStatus)
					.text(newStatus.toUpperCase());

				// Recalculate count badges
				$('.wlgf-kanban-column').each(function() {
					const st = $(this).data('status');
					const c = $(this).find('.wlgf-kanban-card').length;
					$(`#wlgf-count-${st}`).text(c);
				});

				// Sync with DataTables row and select dropdown
				const $tableRow = $(`#${leadIndex}, tr[data-lead-index="${leadIndex}"]`);
				if ($tableRow.length) {
					$tableRow.attr('data-status', newStatus);
					$tableRow.find('.wlgf-status-select, .wlgf-lead-status-select').val(newStatus);
				}
				$(`#wlgf-status-select-${leadIndex}`).val(newStatus);

				// Trigger AJAX update
				const ajaxUrl = (typeof Leads !== 'undefined' && Leads.ajaxUrl) ? Leads.ajaxUrl : 'admin-ajax.php';
				const ajaxNonce = (typeof Leads !== 'undefined' && Leads.nonce) ? Leads.nonce : '';

				$.ajax({
					url: ajaxUrl,
					type: 'POST',
					data: {
						action: 'wlgf_update_lead_status',
						nonce: ajaxNonce,
						form_id: formId,
						lead_index: leadIndex,
						status: newStatus
					},
					success: function(res) {
						if (res && res.success && typeof wlgfToast === 'function') {
							const colTitle = targetCol.find('.wlgf-kanban-col-title').text().trim();
							wlgfToast('Lead #' + leadId + ' moved to ' + colTitle + ' (Saved).', 'success');
						}
					}
				});
			});
		}
	};

	$(document).ready(function() {
		WLGF_Kanban.init();
	});
})(jQuery);
