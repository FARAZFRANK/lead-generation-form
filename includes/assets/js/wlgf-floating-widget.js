/**
 * Lead Generation Form Pro - Floating Multi-Channel Launcher Script
 * Version: 3.2.0
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		const $wrap = $('.wlgf-floating-launcher-wrap');
		if (!$wrap.length) return;

		const $btn = $wrap.find('.wlgf-floating-btn');
		const $menu = $wrap.find('.wlgf-channel-menu');
		const $greeting = $wrap.find('.wlgf-floating-greeting');
		const $greetingClose = $wrap.find('.wlgf-floating-greeting-close');

		// Check sessionStorage for dismissed greeting
		if (sessionStorage.getItem('wlgf_greeting_dismissed') === '1') {
			$greeting.hide();
		}

		// Toggle channel menu
		$btn.on('click', function(e) {
			e.stopPropagation();
			$wrap.toggleClass('wlgf-active');
		});

		// Close greeting
		$greetingClose.on('click', function(e) {
			e.stopPropagation();
			$greeting.fadeOut(200);
			sessionStorage.setItem('wlgf_greeting_dismissed', '1');
		});

		// Close when clicking outside
		$(document).on('click', function(e) {
			if (!$(e.target).closest('.wlgf-floating-launcher-wrap').length) {
				$wrap.removeClass('wlgf-active');
			}
		});

		// Lead Form Channel Trigger -> Open Lightbox Modal
		$('.wlgf-channel-trigger-form').on('click', function(e) {
			e.preventDefault();
			const formId = $(this).data('form-id');
			$wrap.removeClass('wlgf-active');

			if (formId) {
				const $modal = $('#wlgf-form-modal-' + formId);
				if ($modal.length) {
					$modal.addClass('wlgf-modal-open');
					$('body').addClass('wlgf-modal-lock');
				}
			}
		});

		// Close Modal Handlers
		$(document).on('click', '.wlgf-modal-overlay, .wlgf-modal-close-btn', function(e) {
			if (e.target === this || $(this).hasClass('wlgf-modal-close-btn') || $(this).closest('.wlgf-modal-close-btn').length) {
				$('.wlgf-modal-overlay').removeClass('wlgf-modal-open');
				$('body').removeClass('wlgf-modal-lock');
			}
		});

		$(document).keyup(function(e) {
			if (e.key === 'Escape') {
				$('.wlgf-modal-overlay').removeClass('wlgf-modal-open');
				$('body').removeClass('wlgf-modal-lock');
				$wrap.removeClass('wlgf-active');
			}
		});
	});
})(jQuery);
