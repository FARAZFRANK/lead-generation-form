/**
 * Lead Generation Form Pro - HTML5 Canvas Signature Pad Engine (Forminator Challenger)
 * High-performance, zero-dependency, touch & mouse smooth pen stroke capture.
 * Version: 3.2.0
 */

(function($) {
	'use strict';

	function initSignaturePad($wrapper) {
		const canvas = $wrapper.find('canvas.wlgf-signature-canvas')[0];
		const $input = $wrapper.find('input[type="hidden"]');
		const $clearBtn = $wrapper.find('.wlgf-signature-clear-btn');
		if (!canvas) return;

		const ctx = canvas.getContext('2d');
		let isDrawing = false;
		let lastX = 0;
		let lastY = 0;
		let hasStrokes = false;

		// Handle High-DPI screens
		function resizeCanvas() {
			const rect = canvas.getBoundingClientRect();
			const dpr = window.devicePixelRatio || 1;
			if (rect.width === 0) return;

			// Save existing image if any
			let dataUrl = canvas.toDataURL();
			canvas.width = rect.width * dpr;
			canvas.height = (rect.height || 160) * dpr;
			ctx.scale(dpr, dpr);

			ctx.strokeStyle = '#0f172a';
			ctx.lineWidth = 2.2;
			ctx.lineCap = 'round';
			ctx.lineJoin = 'round';

			if (hasStrokes && dataUrl) {
				const img = new Image();
				img.onload = function() {
					ctx.drawImage(img, 0, 0, rect.width, rect.height || 160);
				};
				img.src = dataUrl;
			}
		}

		resizeCanvas();
		$(window).on('resize', resizeCanvas);

		function getCoords(e) {
			const rect = canvas.getBoundingClientRect();
			let clientX, clientY;
			if (e.touches && e.touches.length > 0) {
				clientX = e.touches[0].clientX;
				clientY = e.touches[0].clientY;
			} else {
				clientX = e.clientX;
				clientY = e.clientY;
			}
			return {
				x: clientX - rect.left,
				y: clientY - rect.top
			};
		}

		function startStroke(e) {
			e.preventDefault();
			isDrawing = true;
			$wrapper.addClass('wlgf-canvas-focus');
			const pos = getCoords(e);
			lastX = pos.x;
			lastY = pos.y;
			ctx.beginPath();
			ctx.moveTo(lastX, lastY);
		}

		function drawStroke(e) {
			if (!isDrawing) return;
			e.preventDefault();
			const pos = getCoords(e);
			
			// Draw smooth line
			ctx.lineTo(pos.x, pos.y);
			ctx.stroke();
			lastX = pos.x;
			lastY = pos.y;
			hasStrokes = true;
		}

		function endStroke() {
			if (!isDrawing) return;
			isDrawing = false;
			$wrapper.removeClass('wlgf-canvas-focus');
			if (hasStrokes) {
				$input.val(canvas.toDataURL('image/png')).trigger('change');
			}
		}

		// Mouse Events
		canvas.addEventListener('mousedown', startStroke);
		canvas.addEventListener('mousemove', drawStroke);
		window.addEventListener('mouseup', endStroke);

		// Touch Events
		canvas.addEventListener('touchstart', startStroke, { passive: false });
		canvas.addEventListener('touchmove', drawStroke, { passive: false });
		window.addEventListener('touchend', endStroke);

		function clearSignature() {
			ctx.save();
			ctx.setTransform(1, 0, 0, 1, 0, 0);
			ctx.clearRect(0, 0, canvas.width, canvas.height);
			ctx.restore();
			hasStrokes = false;
			$input.val('').trigger('change');
		}

		// Clear Button
		$clearBtn.on('click', function(e) {
			e.preventDefault();
			clearSignature();
		});

		// Listen to custom clear and form reset events
		$wrapper.on('wlgf-signature-clear', clearSignature);
		$wrapper.closest('form').on('reset', function() {
			setTimeout(clearSignature, 10);
		});
	}

	$(document).ready(function() {
		$('.wlgf-signature-container').each(function() {
			initSignaturePad($(this));
		});

		// Trigger resize when modal opens
		$(document).on('click', '.wlgf-channel-trigger-form, .wlgf-trigger-popup', function() {
			setTimeout(function() {
				$('.wlgf-signature-container').each(function() {
					initSignaturePad($(this));
				});
			}, 300);
		});
	});
})(jQuery);
