/**
 * Lead Generation Form - Native Gutenberg Block
 */

(function(wp) {
	'use strict';

	if (!wp || !wp.blocks || !wp.element) {
		return;
	}

	const { registerBlockType } = wp.blocks;
	const { createElement: el } = wp.element;
	const { InspectorControls } = wp.blockEditor || wp.editor || {};
	const { PanelBody, SelectControl, Placeholder } = wp.components || {};
	const __ = (wp.i18n && wp.i18n.__) ? wp.i18n.__ : function(s) { return s; };

	const formsList = (window.WLGF_BlockData && window.WLGF_BlockData.forms) ? window.WLGF_BlockData.forms : [];

	registerBlockType('wlgf/lead-form', {
		title: __('Lead Generation Form', 'lead-generation-form'),
		description: __('Embed a customized Lead Generation Form directly into your content.', 'lead-generation-form'),
		icon: 'feedback',
		category: 'widgets',
		keywords: [
			__('lead', 'lead-generation-form'),
			__('form', 'lead-generation-form'),
			__('contact', 'lead-generation-form'),
			__('survey', 'lead-generation-form'),
			'wlgf',
			__('lead generation', 'lead-generation-form')
		],
		attributes: {
			formId: {
				type: 'string',
				default: formsList.length > 0 ? String(formsList[0].value) : '1'
			}
		},
		edit: function(props) {
			const { attributes, setAttributes } = props;
			const formId = attributes.formId;

			const selectedForm = formsList.find(function(f) {
				return String(f.value) === String(formId);
			});
			const formTitle = selectedForm ? selectedForm.label : ('Form #' + formId);

			const controls = [];

			if (InspectorControls && PanelBody && SelectControl) {
				controls.push(
					el(InspectorControls, { key: 'inspector' },
						el(PanelBody, { title: __('Form Settings', 'lead-generation-form'), initialOpen: true },
							el(SelectControl, {
								label: __('Select Form to Embed', 'lead-generation-form'),
								value: formId,
								options: formsList,
								onChange: function(newId) {
									setAttributes({ formId: newId });
								}
							})
						)
					)
				);
			}

			if (Placeholder && SelectControl) {
				controls.push(
					el(Placeholder, {
						key: 'placeholder',
						icon: 'feedback',
						label: __('Lead Generation Form: ', 'lead-generation-form') + formTitle,
						instructions: __('Choose a form from the dropdown to embed on this page.', 'lead-generation-form')
					},
						el(SelectControl, {
							value: formId,
							options: formsList,
							onChange: function(newId) {
								setAttributes({ formId: newId });
							}
						})
					)
				);
			} else {
				controls.push(
					el('div', { key: 'fallback', className: 'wlgf-block-fallback' },
						'Lead Generation Form [WLFG id="' + formId + '"]'
					)
				);
			}

			return el('div', { className: 'wlgf-gutenberg-block-wrapper' }, controls);
		},
		save: function() {
			return null; // Server-side dynamic render
		}
	});
})(window.wp);
