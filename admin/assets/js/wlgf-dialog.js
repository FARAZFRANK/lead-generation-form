/**
 * Lead Generation Form - Modern Universal Dialog & Alert System (wlgf-dialog.js)
 * High-performance, accessible, and beautifully animated replacement for native window.alert and window.confirm.
 */
(function(window, document, $) {
	'use strict';

	window.WLGF_Dialog = {
		/**
		 * Internal DOM builder for dialog modals
		 */
		createModal: function(options) {
			$('#wlgf-dynamic-dialog-overlay').remove();

			const opts = $.extend({
				title: 'Confirmation',
				message: '',
				type: 'warning', // 'warning', 'danger', 'info', 'success'
				confirmText: 'Confirm',
				cancelText: 'Cancel',
				isConfirm: false,
				icon: null
			}, typeof options === 'string' ? { message: options } : options);

			let iconHtml = '';
			let typeClass = 'wlgf-dialog--warning';

			if (opts.type === 'danger') {
				typeClass = 'wlgf-dialog--danger';
				iconHtml = '<div class="wlgf-dialog__icon wlgf-dialog__icon--danger"><i class="fa-solid fa-triangle-exclamation"></i></div>';
			} else if (opts.type === 'success') {
				typeClass = 'wlgf-dialog--success';
				iconHtml = '<div class="wlgf-dialog__icon wlgf-dialog__icon--success"><i class="fa-solid fa-circle-check"></i></div>';
			} else if (opts.type === 'info') {
				typeClass = 'wlgf-dialog--info';
				iconHtml = '<div class="wlgf-dialog__icon wlgf-dialog__icon--info"><i class="fa-solid fa-circle-info"></i></div>';
			} else {
				typeClass = 'wlgf-dialog--warning';
				iconHtml = '<div class="wlgf-dialog__icon wlgf-dialog__icon--warning"><i class="fa-solid fa-triangle-exclamation"></i></div>';
			}

			if (opts.icon) {
				iconHtml = '<div class="wlgf-dialog__icon wlgf-dialog__icon--' + (opts.type || 'info') + '"><i class="' + opts.icon + '"></i></div>';
			}

			let footerHtml = '';
			if (opts.isConfirm) {
				const confirmBtnClass = opts.type === 'danger' ? 'wlgf-btn--danger' : 'wlgf-btn--primary';
				footerHtml = `
					<button type="button" class="wlgf-btn wlgf-btn--secondary wlgf-dialog-cancel-btn">${opts.cancelText}</button>
					<button type="button" class="wlgf-btn ${confirmBtnClass} wlgf-dialog-confirm-btn">${opts.confirmText}</button>
				`;
			} else {
				footerHtml = `
					<button type="button" class="wlgf-btn wlgf-btn--primary wlgf-dialog-confirm-btn">${opts.confirmText || 'Got It'}</button>
				`;
			}

			const $overlay = $(`
				<div id="wlgf-dynamic-dialog-overlay" class="wlgf-modal-overlay wlgf-dialog-overlay ${typeClass}" role="dialog" aria-modal="true">
					<div class="wlgf-modal wlgf-dialog-modal">
						<div class="wlgf-dialog-header">
							${iconHtml}
							<div class="wlgf-dialog-header__text">
								<h3 class="wlgf-dialog__title">${opts.title}</h3>
							</div>
							<button type="button" class="wlgf-dialog-close-btn" aria-label="Close">&times;</button>
						</div>
						<div class="wlgf-dialog__body">
							<div class="wlgf-dialog__message">${opts.message}</div>
						</div>
						<div class="wlgf-dialog__footer">
							${footerHtml}
						</div>
					</div>
				</div>
			`);

			$('body').append($overlay);

			// Trigger opening animation smoothly
			requestAnimationFrame(function() {
				$overlay.addClass('wlgf-modal-overlay--active');
			});

			return $overlay;
		},

		/**
		 * Beautiful confirm modal returning a Promise or invoking callback
		 */
		confirm: function(options, callback) {
			return new Promise(function(resolve) {
				let opts = {};
				if (typeof options === 'string') {
					opts = { message: options, isConfirm: true, type: 'warning', title: 'Please Confirm' };
				} else {
					opts = $.extend({ isConfirm: true, type: 'warning', title: 'Please Confirm' }, options);
				}

				const $modal = window.WLGF_Dialog.createModal(opts);
				let resolved = false;

				const finish = function(result) {
					if (resolved) return;
					resolved = true;
					$(document).off('keydown.wlgfDialog');
					$modal.removeClass('wlgf-modal-overlay--active');
					setTimeout(function() { $modal.remove(); }, 220);
					if (typeof callback === 'function') callback(result);
					resolve(result);
				};

				$modal.find('.wlgf-dialog-confirm-btn').on('click', function(e) {
					e.preventDefault();
					finish(true);
				}).focus();

				$modal.find('.wlgf-dialog-cancel-btn, .wlgf-dialog-close-btn').on('click', function(e) {
					e.preventDefault();
					finish(false);
				});

				$modal.on('click', function(e) {
					if (e.target === this) finish(false);
				});

				$(document).on('keydown.wlgfDialog', function(e) {
					if (e.key === 'Escape') finish(false);
					else if (e.key === 'Enter') finish(true);
				});
			});
		},

		/**
		 * Beautiful alert notice modal returning a Promise or invoking callback
		 */
		alert: function(options, callback) {
			return new Promise(function(resolve) {
				let opts = {};
				if (typeof options === 'string') {
					opts = { message: options, isConfirm: false, type: 'info', title: 'Notice' };
				} else {
					opts = $.extend({ isConfirm: false, type: 'info', title: 'Notice' }, options);
				}

				const $modal = window.WLGF_Dialog.createModal(opts);
				let resolved = false;

				const finish = function() {
					if (resolved) return;
					resolved = true;
					$(document).off('keydown.wlgfDialog');
					$modal.removeClass('wlgf-modal-overlay--active');
					setTimeout(function() { $modal.remove(); }, 220);
					if (typeof callback === 'function') callback();
					resolve(true);
				};

				$modal.find('.wlgf-dialog-confirm-btn, .wlgf-dialog-close-btn').on('click', function(e) {
					e.preventDefault();
					finish();
				}).focus();

				$modal.on('click', function(e) {
					if (e.target === this) finish();
				});

				$(document).on('keydown.wlgfDialog', function(e) {
					if (e.key === 'Escape' || e.key === 'Enter') finish();
				});
			});
		},

		/**
		 * Floating Toast Notification
		 */
		toast: function(message, type, duration) {
			let msg = message;
			if (typeof message === 'object' && message !== null) {
				msg = message.message || message.msg || message.text || '';
				type = message.type || type;
				duration = message.duration || duration;
			}

			type = type || 'info';
			duration = duration || 3500;

			let $container = $('#wlgf-toast-container');
			if (!$container.length) {
				$container = $('<div id="wlgf-toast-container" class="wlgf-toast-container"></div>');
				$('body').append($container);
			}

			let icon = 'fa-solid fa-circle-info';
			if (type === 'success') icon = 'fa-solid fa-circle-check';
			else if (type === 'warning') icon = 'fa-solid fa-triangle-exclamation';
			else if (type === 'error' || type === 'danger') icon = 'fa-solid fa-circle-exclamation';

			const $toast = $(`
				<div class="wlgf-toast wlgf-toast--${type}">
					<div class="wlgf-toast__icon"><i class="${icon}"></i></div>
					<div class="wlgf-toast__message">${msg}</div>
					<button type="button" class="wlgf-toast__close" aria-label="Close">&times;</button>
				</div>
			`);

			$container.append($toast);
			requestAnimationFrame(function() {
				$toast.addClass('wlgf-toast--active');
			});

			const removeToast = function() {
				$toast.removeClass('wlgf-toast--active');
				setTimeout(function() { $toast.remove(); }, 300);
			};

			$toast.find('.wlgf-toast__close').on('click', removeToast);
			setTimeout(removeToast, duration);
		}
	};

	window.wlgfConfirm = window.WLGF_Dialog.confirm;
	window.wlgfAlert = window.WLGF_Dialog.alert;
	window.wlgfToast = window.WLGF_Dialog.toast;

})(window, document, jQuery);
