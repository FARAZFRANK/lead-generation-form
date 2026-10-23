/**
 * Lead Generation Form Pro - Smart Conversion Triggers Engine (OptinMonster / Hustle Challenger)
 * Handles Exit-Intent, Scroll Depth %, Time Delay, and Click-to-Open Lightbox Popups.
 * Version: 3.2.0
 */

(function($) {
	'use strict';

	window.WLGF_Popups = {
		init: function() {
			if (typeof wlgfPopupsConfig === 'undefined' || !Array.isArray(wlgfPopupsConfig)) {
				return;
			}

			wlgfPopupsConfig.forEach(function(cfg) {
				WLGF_Popups.setupFormTriggers(cfg);
			});

			WLGF_Popups.setupClickTriggers();
			WLGF_Popups.setupCloseHandlers();
		},

		/**
		 * Check if a popup is suppressed due to frequency capping
		 */
		isSuppressed: function(formId, days) {
			if (!days || days <= 0) return false;
			const key = 'wlgf_suppressed_' + formId;
			const timestamp = localStorage.getItem(key);
			if (!timestamp) return false;

			const expiryMs = parseInt(days, 10) * 24 * 60 * 60 * 1000;
			const elapsed = Date.now() - parseInt(timestamp, 10);
			return elapsed < expiryMs;
		},

		suppress: function(formId, days) {
			if (!days || days <= 0) days = 7;
			localStorage.setItem('wlgf_suppressed_' + formId, Date.now().toString());
		},

		/**
		 * Open the display module (Popup, Slide-in, or Sticky Bar)
		 */
		open: function(formId, mode) {
			if ($('#wlgf-modal-' + formId).hasClass('wlgf-modal-open') ||
				$('#wlgf-slidein-' + formId).hasClass('wlgf-slidein-active') ||
				$('#wlgf-bar-' + formId).hasClass('wlgf-bar-active')) {
				return; // Already open
			}

			if (mode === 'popup') {
				const $modal = $('#wlgf-modal-' + formId);
				if ($modal.length) {
					$modal.addClass('wlgf-modal-open');
					$('body').addClass('wlgf-modal-lock');
				}
			} else if (mode === 'slidein') {
				const $slidein = $('#wlgf-slidein-' + formId);
				if ($slidein.length) {
					$slidein.addClass('wlgf-slidein-active');
				}
			} else if (mode === 'sticky_bar') {
				const $bar = $('#wlgf-bar-' + formId);
				if ($bar.length) {
					$bar.addClass('wlgf-bar-active');
				}
			}
		},

		/**
		 * Setup behavioral triggers for an individual form configuration
		 */
		setupFormTriggers: function(cfg) {
			const formId = cfg.formId;
			const mode = cfg.mode || 'popup';
			const trigger = cfg.trigger || 'time_delay';
			const delaySec = parseInt(cfg.delaySeconds, 10) || 5;
			const scrollPct = parseInt(cfg.scrollPercent, 10) || 50;
			const freqDays = parseInt(cfg.frequencyDays, 10) || 7;

			if (WLGF_Popups.isSuppressed(formId, freqDays)) {
				return;
			}

			let triggered = false;

			// 1. Time Delay Trigger
			if (trigger === 'time_delay') {
				setTimeout(function() {
					if (!triggered) {
						triggered = true;
						WLGF_Popups.open(formId, mode);
					}
				}, delaySec * 1000);
			}

			// 2. Exit-Intent Trigger
			else if (trigger === 'exit_intent') {
				// Desktop: Mouse leaves viewport towards top
				$(document).on('mouseleave.wlgf_' + formId, function(e) {
					if (e.clientY <= 10 && !triggered) {
						triggered = true;
						$(document).off('mouseleave.wlgf_' + formId);
						WLGF_Popups.open(formId, mode);
					}
				});

				// Mobile: Fast scroll up detection
				let lastScrollTop = 0;
				let lastScrollTime = Date.now();
				$(window).on('scroll.wlgf_exit_' + formId, function() {
					const st = window.pageYOffset || document.documentElement.scrollTop;
					const now = Date.now();
					const diff = lastScrollTop - st;
					const timeDiff = now - lastScrollTime;

					// Rapid upward scroll gesture indicative of leaving
					if (diff > 150 && timeDiff < 100 && st > 200 && !triggered) {
						triggered = true;
						$(window).off('scroll.wlgf_exit_' + formId);
						WLGF_Popups.open(formId, mode);
					}
					lastScrollTop = st;
					lastScrollTime = now;
				});
			}

			// 3. Scroll Depth Trigger
			else if (trigger === 'scroll_depth') {
				$(window).on('scroll.wlgf_scroll_' + formId, function() {
					if (triggered) return;
					const scrollTop = $(window).scrollTop();
					const docHeight = $(document).height();
					const winHeight = $(window).height();
					const currentPct = (scrollTop / (docHeight - winHeight)) * 100;

					if (currentPct >= scrollPct) {
						triggered = true;
						$(window).off('scroll.wlgf_scroll_' + formId);
						WLGF_Popups.open(formId, mode);
					}
				});
			}
		},

		/**
		 * Setup click-to-open (MonsterLink style) triggers
		 */
		setupClickTriggers: function() {
			// Links with class .wlgf-trigger-popup or href="#wlgf-popup-{ID}"
			$(document).on('click', '.wlgf-trigger-popup, a[href^="#wlgf-popup-"]', function(e) {
				let formId = $(this).data('form-id');
				if (!formId) {
					const href = $(this).attr('href') || '';
					const match = href.match(/#wlgf-popup-(\d+)/);
					if (match) {
						formId = match[1];
					}
				}

				if (formId) {
					e.preventDefault();
					WLGF_Popups.open(formId, 'popup');
				}
			});

			// Floating widget button trigger
			$(document).on('click', '.wlgf-trigger-modal-btn', function(e) {
				e.preventDefault();
				const target = $(this).data('target');
				if (target && $(target).length) {
					$(target).addClass('wlgf-modal-open');
					$('body').addClass('wlgf-modal-lock');
				}
			});

			// Greeting bubble dismiss
			$(document).on('click', '.wlgf-floating-greeting-close', function(e) {
				e.stopPropagation();
				$(this).closest('.wlgf-floating-greeting').fadeOut(200);
			});
		},

		/**
		 * Close buttons and modal dismiss
		 */
		setupCloseHandlers: function() {
			// Modal close
			$(document).on('click', '.wlgf-modal-close-btn, .wlgf-modal-overlay', function(e) {
				if (e.target === this || $(this).hasClass('wlgf-modal-close-btn') || $(this).closest('.wlgf-modal-close-btn').length) {
					const $overlay = $(this).closest('.wlgf-modal-overlay');
					const formId = $overlay.data('form-id');
					$overlay.removeClass('wlgf-modal-open');
					$('body').removeClass('wlgf-modal-lock');
					if (formId) {
						WLGF_Popups.suppress(formId, 7);
					}
				}
			});

			// Slide-in close
			$(document).on('click', '.wlgf-slidein-close', function(e) {
				e.preventDefault();
				const $slidein = $(this).closest('.wlgf-slidein-wrap');
				const formId = $slidein.data('form-id');
				$slidein.removeClass('wlgf-slidein-active');
				if (formId) {
					WLGF_Popups.suppress(formId, 7);
				}
			});

			// Sticky bar close
			$(document).on('click', '.wlgf-sticky-bar-close', function(e) {
				e.preventDefault();
				const $bar = $(this).closest('.wlgf-sticky-bar-wrap');
				const formId = $bar.data('form-id');
				$bar.removeClass('wlgf-bar-active');
				if (formId) {
					WLGF_Popups.suppress(formId, 7);
				}
			});

			// Suppress on successful form submission
			$(document).on('wlgf_form_submitted', function(e, data) {
				if (data && data.formId) {
					WLGF_Popups.suppress(data.formId, 30);
				}
			});
		}
	};

	$(document).ready(function() {
		WLGF_Popups.init();
	});
})(jQuery);
