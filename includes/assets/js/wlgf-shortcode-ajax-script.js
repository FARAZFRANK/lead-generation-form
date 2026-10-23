/**
 * Lead Generation Form - Public Shortcode AJAX Submission Script
 */

jQuery(document).ready(function($) {
    $('form[id^="wlgf-form-"]').each(function() {
        const $form = $(this);
        const formIdAttr = $form.find('input[name="wlgf_form_id"]').val() || $form.attr('id').replace('wlgf-form-', '');
        const formConfig = (window.WLGFAjaxData && window.WLGFAjaxData[formIdAttr]) ? window.WLGFAjaxData[formIdAttr] : (window.WLGFAjax || {});
        const shortcodeConfig = (window.WLGFShortcodeData && window.WLGFShortcodeData[formIdAttr]) ? window.WLGFShortcodeData[formIdAttr] : (window.WLGFShortcode || {});

        // Set form load timestamp for spam protection
        const loadTimeInput = $form.find('#wlgf_form_load_time');
        if (loadTimeInput.length) {
            loadTimeInput.val(Date.now());
        }

        // Function to check reCAPTCHA token freshness
        function isRecaptchaExpired() {
            if (shortcodeConfig.Recaptcha != 1 || !shortcodeConfig.SiteKey) {
                return false;
            }
            if (typeof grecaptcha !== 'undefined') {
                return false; // grecaptcha will refresh token on submit
            }
            const currentTime = Date.now();
            const formLoadTime = parseInt(loadTimeInput.val(), 10) || currentTime;
            return (currentTime - formLoadTime) > 120000; // 2 minutes
        }

        // Clear field validation error states on user input or interaction (never on focus)
        $form.on('input change', 'input, textarea, select', function() {
            $(this).closest('.wlgf-field-wrap').removeClass('wlgf-field--has-error').find('.wlgf-inline-error').remove();
            if (!$form.hasClass('wlgf-is-submitting')) {
                $form.find('button[type="submit"], input[type="submit"]').prop('disabled', false);
            }
        });
        $form.on('click', '.wlgf-star, .wlgf-nps-btn', function() {
            $(this).closest('.wlgf-field-wrap').removeClass('wlgf-field--has-error').find('.wlgf-inline-error').remove();
            if (!$form.hasClass('wlgf-is-submitting')) {
                $form.find('button[type="submit"], input[type="submit"]').prop('disabled', false);
            }
        });
        $form.on('change', '.wlgf-dropzone__input', function() {
            $(this).closest('.wlgf-field-wrap').removeClass('wlgf-field--has-error').find('.wlgf-inline-error').remove();
            if (!$form.hasClass('wlgf-is-submitting')) {
                $form.find('button[type="submit"], input[type="submit"]').prop('disabled', false);
            }
        });
        $form.on('keyup', 'input, textarea', function() {
            if (!$form.hasClass('wlgf-is-submitting')) {
                $form.find('button[type="submit"], input[type="submit"]').prop('disabled', false);
            }
        });

        // AJAX submit handler
        $form.on('submit', function(e) {
            e.preventDefault();

            const $currentForm = $(this);
            if ($currentForm.hasClass('wlgf-is-submitting')) {
                return false;
            }
            const $responseOutput = $currentForm.find('#wlgf-response-output');
            const $recaptchaOutput = $currentForm.find('#wlgf-recaptcha-output');
            const $spinner = $currentForm.find('.wlgf-spinner');
            const $submitBtns = $currentForm.find('button[type="submit"], input[type="submit"]');

            $responseOutput.empty().hide();
            $recaptchaOutput.empty().hide();

            // Check if reCAPTCHA is expired
            if (isRecaptchaExpired()) {
                $recaptchaOutput.html('<div class="wlgf-alert wlgf-alert--danger">The security verification has expired. Please reload the page and try again.</div>').show();
                $submitBtns.prop('disabled', false);
                $spinner.hide();
                return false;
            }

            // Clear any previous field errors
            $currentForm.find('.wlgf-field-wrap').removeClass('wlgf-field--has-error');
            $currentForm.find('.wlgf-inline-error').remove();

            let isValid = true;
            let $firstInvalid = null;

            function markInvalid($wrap, message, $focusTarget) {
                isValid = false;
                $wrap.addClass('wlgf-field--has-error');
                if (!$wrap.find('.wlgf-inline-error').length) {
                    $wrap.append('<div class="wlgf-inline-error" role="alert"><i class="fa-solid fa-circle-exclamation"></i> ' + message + '</div>');
                }
                if (!$firstInvalid) {
                    $firstInvalid = $focusTarget || $wrap;
                }
            }

            // Validate all visible, enabled fields in document DOM order
            $currentForm.find('.wlgf-field-wrap').each(function() {
                const $wrap = $(this);

                // Skip non-input wrappers or hidden elements (unless in active step card)
                if ($wrap.is(':hidden') && !$wrap.closest('.wlgf-step-card--active').length) {
                    return;
                }

                const fieldType = ($wrap.attr('data-field-type') || '').toLowerCase();
                const isRequired = $wrap.attr('data-required') === '1';

                if (fieldType === 'nps-matrix') {
                    const req = isRequired || $wrap.find('.wlgf-nps-radio[required]').length > 0;
                    if (req && !$wrap.find('.wlgf-nps-radio:checked').length) {
                        markInvalid($wrap, 'Please select a score from 0 to 10.', $wrap.find('.wlgf-nps-btn').first());
                    }
                } else if (fieldType === 'star-rating') {
                    const req = isRequired || $wrap.find('.wlgf-star-radio[required]').length > 0;
                    if (req && !$wrap.find('.wlgf-star-radio:checked').length) {
                        markInvalid($wrap, 'Please select a rating (1 to 5 stars).', $wrap.find('.wlgf-star').first());
                    }
                } else if (fieldType === 'radio-group') {
                    const req = isRequired || $wrap.find('input[type="radio"][required]').length > 0;
                    if (req && !$wrap.find('input[type="radio"]:checked').length) {
                        markInvalid($wrap, 'Please select an option.', $wrap.find('input[type="radio"]').first());
                    }
                } else if (fieldType === 'checkbox-group') {
                    const req = isRequired || $wrap.find('input[type="checkbox"][required]').length > 0;
                    if (req && !$wrap.find('input[type="checkbox"]:checked').length) {
                        markInvalid($wrap, 'Please select at least one option.', $wrap.find('input[type="checkbox"]').first());
                    }
                } else if (fieldType === 'file') {
                    const req = isRequired || $wrap.find('input[type="file"][required]').length > 0;
                    const fileInput = $wrap.find('input[type="file"]')[0];
                    const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                    if (req && !hasFile) {
                        markInvalid($wrap, 'Please upload a required file.', $wrap.find('.wlgf-dropzone'));
                    } else if (hasFile) {
                        const $dzWrap = $wrap.find('.wlgf-dropzone-wrap');
                        const maxSize = parseInt($dzWrap.data('max-size'), 10) || 52428800;
                        const allowedRaw = $dzWrap.data('allowed-types');
                        const allowedExts = allowedRaw ? allowedRaw.toString().toLowerCase().split(',').map(function(s) { return s.trim().replace(/^\./, ''); }).filter(Boolean) : [];
                        let totalSize = 0;
                        let invalidExt = null;
                        for (let f = 0; f < fileInput.files.length; f++) {
                            const fl = fileInput.files[f];
                            totalSize += fl.size;
                            const ext = (fl.name.indexOf('.') !== -1) ? fl.name.split('.').pop().toLowerCase() : '';
                            if (allowedExts.length > 0 && allowedExts.indexOf(ext) === -1) {
                                invalidExt = ext;
                                break;
                            }
                        }
                        if (invalidExt) {
                            markInvalid($wrap, 'Invalid file type ".' + invalidExt + '". Allowed: ' + allowedExts.join(', ').toUpperCase(), $wrap.find('.wlgf-dropzone'));
                        } else if (totalSize > maxSize) {
                            const maxMb = (maxSize / (1024 * 1024)).toFixed(0);
                            markInvalid($wrap, 'Total file size exceeds limit (' + maxMb + 'MB max).', $wrap.find('.wlgf-dropzone'));
                        }
                    }
                } else if (fieldType === 'terms') {
                    const $cb = $wrap.find('input[type="checkbox"]');
                    const req = isRequired || $cb.prop('required');
                    if (req && !$cb.is(':checked')) {
                        markInvalid($wrap, 'You must agree to continue.', $cb);
                    }
                } else if (fieldType === 'signature') {
                    const $sigInput = $wrap.find('input.wlgf-signature-input');
                    const req = isRequired || $sigInput.prop('required') || $sigInput.attr('required');
                    if (req && !$.trim($sigInput.val())) {
                        markInvalid($wrap, 'Please provide your digital signature.', $wrap.find('canvas.wlgf-signature-canvas'));
                    }
                } else {
                    // General inputs: text, email, tel, url, number, date, textarea, select
                    const $inputs = $wrap.find('input, select, textarea').not(':disabled, .wlgf-nps-radio, .wlgf-star-radio, input[type="radio"], input[type="checkbox"], input[type="file"], input[type="hidden"], button');
                    $inputs.each(function() {
                        const $input = $(this);
                        const val = $.trim($input.val() || '');
                        const req = $input.prop('required') || isRequired;

                        if (req && !val) {
                            markInvalid($wrap, 'This field is required.', $input);
                            return;
                        }

                        if (val) {
                            const type = ($input.attr('type') || '').toLowerCase();
                            if (type === 'email') {
                                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                                if (!emailPattern.test(val)) {
                                    markInvalid($wrap, 'Please enter a valid email address.', $input);
                                }
                            } else if (type === 'date') {
                                const datePattern = /^\d{4}-\d{2}-\d{2}$/;
                                if (!datePattern.test(val)) {
                                    markInvalid($wrap, 'Please enter a valid date in YYYY-MM-DD format.', $input);
                                }
                            } else if (type === 'url') {
                                const urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
                                if (!urlPattern.test(val)) {
                                    markInvalid($wrap, 'Please enter a valid URL.', $input);
                                }
                            }
                        }
                    });
                }
            });

            if (!isValid && $firstInvalid) {
                $currentForm.removeClass('wlgf-is-submitting');
                $submitBtns.prop('disabled', false);
                $spinner.hide();
                const $scrollWrap = $firstInvalid.closest('.wlgf-field-wrap');
                if ($scrollWrap.length && $scrollWrap[0] && typeof $scrollWrap[0].scrollIntoView === 'function') {
                    $scrollWrap[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                if (typeof $firstInvalid.focus === 'function') {
                    $firstInvalid.focus();
                }
                return false;
            }

            $currentForm.addClass('wlgf-is-submitting');
            $submitBtns.prop('disabled', true);
            $spinner.show();

            function sendFormAjax() {
                const formData = new FormData($currentForm[0]);

                $.ajax({
                    type: 'POST',
                    url: window.location.href,
                    data: formData,
                    processData: false,
                    contentType: false,
                success: function(response) {
                    $currentForm.removeClass('wlgf-is-submitting');
                    $spinner.hide();
                    $submitBtns.prop('disabled', false);

                    // Check if response contains an error or warning alert (e.g. reCAPTCHA failure, spam, closed form)
                    if (response) {
                        const $resp = $('<div>').append($.parseHTML(response, document, false));
                        const $errorAlert = $resp.find('.wlgf-alert--danger, .wlgf-alert--warning, .wlgf-alert');
                        const hasSuccessMsg = $resp.find('#wlgf-msg').length > 0;

                        if ($errorAlert.length && !hasSuccessMsg) {
                            $responseOutput.html(response).show();
                            if (typeof wlgfToast === 'function') {
                                const errorText = $errorAlert.text().trim() || 'Submission failed. Please check the errors.';
                                wlgfToast(errorText, 'error', 6000);
                            }
                            if ($responseOutput.length && $responseOutput.offset()) {
                                $('html, body').animate({
                                    scrollTop: Math.max(0, $responseOutput.offset().top - 120)
                                }, 300);
                            }
                            return;
                        }
                    }

                    // 1. Native DOM form reset
                    if ($currentForm[0] && typeof $currentForm[0].reset === 'function') {
                        $currentForm[0].reset();
                    }

                    // 2. Explicitly clear all text inputs, textareas, numbers, dates
                    $currentForm.find('input[type="text"], input[type="email"], input[type="tel"], input[type="url"], input[type="number"], input[type="date"], textarea').val('');

                    // 3. Clear all radio buttons and checkboxes (removes default checked state)
                    $currentForm.find('input[type="radio"], input[type="checkbox"]').prop('checked', false).removeAttr('checked');

                    // 4. Reset select dropdowns to initial option
                    $currentForm.find('select').each(function() {
                        $(this).prop('selectedIndex', 0);
                    });

                    // 5. Clear Interactive NPS Matrix visuals and radios
                    $currentForm.find('.wlgf-nps-btn').removeClass('wlgf-nps-btn--selected').attr('aria-checked', 'false');
                    $currentForm.find('.wlgf-nps-radio').prop('checked', false).removeAttr('checked');

                    // 6. Clear Interactive Star Rating visuals and radios
                    $currentForm.find('.wlgf-star').removeClass('wlgf-star--active wlgf-star--hover').attr('aria-checked', 'false');
                    $currentForm.find('.wlgf-star-radio').prop('checked', false).removeAttr('checked');

                    // 7. Clear Dropzone file uploads
                    $currentForm.find('.wlgf-dropzone__input').val('');
                    $currentForm.find('.wlgf-file-list').empty();

                    // 8. Reset dynamic calculations
                    $currentForm.find('.wlgf-calc-val').text('0.00');
                    $currentForm.find('.wlgf-calc-hidden-input').val('0.00');

                    // 9. Reset digital signature pads if any
                    $currentForm.find('.wlgf-signature-clear-btn, .wlgf-signature-pad-clear').trigger('click');
                    $currentForm.find('.wlgf-signature-container').each(function() {
                        const canvas = $(this).find('canvas.wlgf-signature-canvas')[0];
                        if (canvas) {
                            const ctx = canvas.getContext('2d');
                            if (ctx) {
                                ctx.save();
                                ctx.setTransform(1, 0, 0, 1, 0, 0);
                                ctx.clearRect(0, 0, canvas.width, canvas.height);
                                ctx.restore();
                            }
                        }
                        $(this).find('input.wlgf-signature-input, input[type="hidden"]').val('').trigger('change');
                        $(this).trigger('wlgf-signature-clear');
                    });

                    // 10. Refresh form load timestamp and remove draft token
                    $currentForm.find('#wlgf_form_load_time').val(Date.now());
                    $currentForm.find('input[name="wlgf_draft_token"]').remove();

                    // 11. Reset multi-step wizard to Step 1
                    if (window.WLGFMultiStep && typeof window.WLGFMultiStep.reset === 'function') {
                        window.WLGFMultiStep.reset($currentForm);
                    } else {
                        const $container = $currentForm.closest('.wlgf-container');
                        const $stepCards = $currentForm.find('.wlgf-step-card');
                        if ($stepCards.length > 1) {
                            const totalSteps = $stepCards.length;
                            $stepCards.removeClass('wlgf-step-card--active');
                            $stepCards.filter('[data-step="1"]').addClass('wlgf-step-card--active');

                            const $progressBar = $container.find('.wlgf-step-progress');
                            if ($progressBar.length) {
                                const pct = Math.round((1 / totalSteps) * 100);
                                $progressBar.find('.wlgf-step-progress__text').text('Step 1 of ' + totalSteps);
                                $progressBar.find('.wlgf-step-progress__pct').text(pct + '%');
                                $progressBar.find('.wlgf-step-progress__fill').css('width', pct + '%');
                            }

                            const $indicators = $container.find('.wlgf-step-indicators');
                            if ($indicators.length) {
                                $indicators.find('.wlgf-step-indicator').each(function() {
                                    const stepNum = parseInt($(this).data('step'), 10);
                                    $(this).removeClass('wlgf-step-indicator--active wlgf-step-indicator--completed');
                                    if (stepNum === 1) {
                                        $(this).addClass('wlgf-step-indicator--active');
                                    }
                                });
                            }
                        }
                    }

                    // 12. Trigger reset & change for conditional logic re-evaluation
                    $currentForm.trigger('reset').trigger('change');

                    // 13. Ensure submit button remains active and ready for next submission
                    $submitBtns.prop('disabled', false);

                    let redirectUrl = null;
                    let confirmationHtml = formConfig.ConfirmationHTML || (window.WLGFAjax && window.WLGFAjax.ConfirmationHTML);

                    let respFadeout = null;
                    let isPaid = false;
                    if (response) {
                        const $resp = $('<div>').append($.parseHTML(response, document, false));
                        const $msgDiv = $resp.find('#wlgf-msg');
                        if ($msgDiv.length) {
                            if ($msgDiv.data('redirect-url')) {
                                redirectUrl = $msgDiv.data('redirect-url');
                            }
                            if (typeof $msgDiv.data('fadeout') !== 'undefined') {
                                respFadeout = parseInt($msgDiv.data('fadeout'), 10);
                            }
                            if ($msgDiv.data('is-paid') == '1' || $msgDiv.data('is-paid') === 1) {
                                isPaid = true;
                            }
                            if ($msgDiv.find('.wlgf-confirmation-card').length) {
                                confirmationHtml = $msgDiv.html();
                            }
                        }
                    }

                    if (!confirmationHtml) {
                        const msg = formConfig.UserMsg || (window.WLGFAjax && window.WLGFAjax.UserMsg) || 'Thank you! Your query has been successfully submitted.';
                        confirmationHtml = '<div class="wlgf-confirmation-card wlgf-confirmation--modern-card wlgf-confirmation--animated" role="alert"><div class="wlgf-confirmation-badge"><i class="fa-solid fa-circle-check"></i></div><div class="wlgf-confirmation-content"><h4 class="wlgf-confirmation-title" style="color: var(--wlgf-conf-heading, #14532d) !important;">Thank you! Submission Received</h4><div class="wlgf-confirmation-desc" style="color: var(--wlgf-conf-text, #166534) !important;">' + $('<div>').text(msg).html() + '</div></div></div>';
                    }

                    // Render confirmation card immediately
                    $responseOutput.html(confirmationHtml).show();

                    // Instant toast notification for feedback
                    if (typeof wlgfToast === 'function') {
                        if (isPaid) {
                            wlgfToast('Payment & order completed successfully!', 'success', 8000);
                        } else {
                            wlgfToast('Thank you! Submission received.', 'success', 5000);
                        }
                    }

                    // Auto fadeout submission message based on configured duration (skip if paid or fadeout is 0)
                    if ($responseOutput.data('wlgfFadeTimer')) {
                        clearTimeout($responseOutput.data('wlgfFadeTimer'));
                    }
                    if (!isPaid && respFadeout !== 0) {
                        let configuredFadeSec = formConfig.FadeoutSeconds || (window.WLGFAjax && window.WLGFAjax.FadeoutSeconds);
                        if (!configuredFadeSec && respFadeout) {
                            configuredFadeSec = respFadeout;
                        }
                        const fadeSec = Math.max(5, Math.min(50, parseInt(configuredFadeSec, 10) || 10));
                        const fadeTimer = setTimeout(function() {
                            $responseOutput.fadeOut(600, function() {
                                $(this).empty();
                            });
                        }, fadeSec * 1000);
                        $responseOutput.data('wlgfFadeTimer', fadeTimer);
                    }

                    // Smoothly scroll to the confirmation message or container
                    const $stepCards = $currentForm.find('.wlgf-step-card');
                    if ($stepCards.length > 1) {
                        const $containerEl = $currentForm.closest('.wlgf-container');
                        if ($containerEl.length) {
                            const targetScroll = Math.max(0, $containerEl.offset().top - 40);
                            $('html, body').animate({ scrollTop: targetScroll }, 350);
                        }
                    } else if ($responseOutput.length) {
                        const targetScroll = Math.max(0, $responseOutput.offset().top - 120);
                        $('html, body').animate({ scrollTop: targetScroll }, 350);
                    }

                    if (redirectUrl) {
                        setTimeout(function() {
                            window.location.href = redirectUrl;
                        }, 1500);
                    }
                },
                error: function() {
                    $currentForm.removeClass('wlgf-is-submitting');
                    $spinner.hide();
                    $submitBtns.prop('disabled', false);
                    $responseOutput.html('<div class="wlgf-alert wlgf-alert--danger">An error occurred while submitting your inquiry. Please try again.</div>').show();
                }
            });
            }

            if (typeof grecaptcha !== 'undefined' && typeof grecaptcha.execute === 'function' && shortcodeConfig.Recaptcha == 1 && shortcodeConfig.SiteKey) {
                grecaptcha.ready(function() {
                    grecaptcha.execute(shortcodeConfig.SiteKey, { action: 'submit' }).then(function(token) {
                        $currentForm.find('#recaptchaResponse').val(token);
                        $currentForm.find('#wlgf_form_load_time').val(Date.now());
                        sendFormAjax();
                    }).catch(function() {
                        sendFormAjax();
                    });
                });
            } else {
                sendFormAjax();
            }
        });
    });
});