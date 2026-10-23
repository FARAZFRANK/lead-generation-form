/**
 * Lead Generation Form - Public Form Interactive Scripts
 * Multi-Step Wizard Engine (PRO-002) & Visual Conditional Logic Engine (PRO-001)
 */

(function($) {
    'use strict';

    // ==========================================
    // Multi-Step Wizard Engine (PRO-002)
    // ==========================================
    const WLGFMultiStep = {
        init: function() {
            $('.wlgf-container').each(function() {
                const $container = $(this);
                const $form = $container.find('form');
                const $stepCards = $form.find('.wlgf-step-card');

                if ($stepCards.length <= 1) {
                    return;
                }

                const totalSteps = $stepCards.length;

                // Reset Handler: Automatically return to Step 1 on form reset
                $form.on('reset', function() {
                    setTimeout(function() {
                        WLGFMultiStep.goToStep($container, null, 1, totalSteps, true);
                    }, 25);
                });

                // Next Step Handler
                $form.on('click', '.wlgf-btn--next', function(e) {
                    e.preventDefault();
                    const $currentCard = $(this).closest('.wlgf-step-card');
                    const currentStep = parseInt($currentCard.data('step'), 10);

                    // Validate all visible, non-disabled fields within current step card
                    let isValid = true;

                    // Clear previous errors in this step card
                    $currentCard.find('.wlgf-field-wrap').removeClass('wlgf-field--has-error');
                    $currentCard.find('.wlgf-inline-error').remove();

                    // Custom field validation in this step card
                    $currentCard.find('.wlgf-field-wrap[data-field-type="nps-matrix"]').each(function() {
                        const $wrap = $(this);
                        if ($wrap.attr('data-required') === '1' && !$wrap.find('.wlgf-nps-radio:checked').length) {
                            isValid = false;
                            $wrap.addClass('wlgf-field--has-error');
                            if (!$wrap.find('.wlgf-inline-error').length) {
                                $wrap.append('<div class="wlgf-inline-error" role="alert"><i class="fa-solid fa-circle-exclamation"></i> Please select a score from 0 to 10.</div>');
                            }
                        }
                    });

                    $currentCard.find('.wlgf-field-wrap[data-field-type="star-rating"]').each(function() {
                        const $wrap = $(this);
                        if ($wrap.attr('data-required') === '1' && !$wrap.find('.wlgf-star-radio:checked').length) {
                            isValid = false;
                            $wrap.addClass('wlgf-field--has-error');
                            if (!$wrap.find('.wlgf-inline-error').length) {
                                $wrap.append('<div class="wlgf-inline-error" role="alert"><i class="fa-solid fa-circle-exclamation"></i> Please select a rating (1 to 5 stars).</div>');
                            }
                        }
                    });

                    $currentCard.find('.wlgf-field-wrap[data-field-type="radio-group"]').each(function() {
                        const $wrap = $(this);
                        if ($wrap.attr('data-required') === '1' && !$wrap.find('input[type="radio"]:checked').length) {
                            isValid = false;
                            $wrap.addClass('wlgf-field--has-error');
                            if (!$wrap.find('.wlgf-inline-error').length) {
                                $wrap.append('<div class="wlgf-inline-error" role="alert"><i class="fa-solid fa-circle-exclamation"></i> Please select an option.</div>');
                            }
                        }
                    });

                    const $inputs = $currentCard.find('input, select, textarea').not(':disabled, .wlgf-nps-radio, .wlgf-star-radio');

                    for (let i = 0; i < $inputs.length; i++) {
                        const input = $inputs[i];
                        if (typeof input.checkValidity === 'function' && !input.checkValidity()) {
                            if (typeof input.reportValidity === 'function') {
                                input.reportValidity();
                            }
                            isValid = false;
                            break;
                        }
                    }

                    if (!isValid) {
                        return;
                    }

                    const nextStep = currentStep + 1;
                    if (nextStep <= totalSteps) {
                        WLGFMultiStep.goToStep($container, currentStep, nextStep, totalSteps);
                    }
                });

                // Previous Step Handler
                $form.on('click', '.wlgf-btn--prev', function(e) {
                    e.preventDefault();
                    const $currentCard = $(this).closest('.wlgf-step-card');
                    const currentStep = parseInt($currentCard.data('step'), 10);
                    const prevStep = currentStep - 1;

                    if (prevStep >= 1) {
                        WLGFMultiStep.goToStep($container, currentStep, prevStep, totalSteps);
                    }
                });

                // Completed Step Indicator Click Handler (Quick Jump-Back)
                $container.on('click', '.wlgf-step-indicator--completed', function(e) {
                    e.preventDefault();
                    const targetStep = parseInt($(this).data('step'), 10);
                    const $currentCard = $form.find('.wlgf-step-card--active');
                    const currentStep = parseInt($currentCard.data('step'), 10);
                    if (targetStep && targetStep < currentStep) {
                        WLGFMultiStep.goToStep($container, currentStep, targetStep, totalSteps);
                    }
                });
            });
        },

        goToStep: function($container, fromStep, toStep, totalSteps, preventFocus) {
            const $form = $container.find('form');
            const $stepCards = $form.find('.wlgf-step-card');

            $stepCards.removeClass('wlgf-step-card--active');
            $stepCards.filter('[data-step="' + toStep + '"]').addClass('wlgf-step-card--active');

            // Update Progress Bar
            const $progressBar = $container.find('.wlgf-step-progress');
            if ($progressBar.length) {
                const pct = Math.round((toStep / totalSteps) * 100);
                $progressBar.find('.wlgf-step-progress__text').text('Step ' + toStep + ' of ' + totalSteps);
                $progressBar.find('.wlgf-step-progress__pct').text(pct + '%');
                $progressBar.find('.wlgf-step-progress__fill').css('width', pct + '%');
            }

            // Update Numbered Circles & Breadcrumbs Indicators
            const $indicators = $container.find('.wlgf-step-indicators');
            if ($indicators.length) {
                $indicators.find('.wlgf-step-indicator').each(function() {
                    const stepNum = parseInt($(this).data('step'), 10);
                    $(this).removeClass('wlgf-step-indicator--active wlgf-step-indicator--completed');

                    if (stepNum === toStep) {
                        $(this).addClass('wlgf-step-indicator--active');
                    } else if (stepNum < toStep) {
                        $(this).addClass('wlgf-step-indicator--completed');
                    }
                });
            }

            // Scroll container smoothly into view if needed
            const containerTop = $container.offset().top - 40;
            if ($(window).scrollTop() > containerTop) {
                $('html, body').animate({ scrollTop: containerTop }, 250);
            }

            // Focus first input of active card unless prevented
            if (!preventFocus) {
                $stepCards.filter('[data-step="' + toStep + '"]').find('input, select, textarea').not(':disabled').first().focus();
            }
        },

        reset: function($formOrContainer) {
            const $container = $formOrContainer.closest('.wlgf-container');
            const $form = $container.find('form');
            const $stepCards = $form.find('.wlgf-step-card');
            if ($stepCards.length <= 1) {
                return;
            }
            const totalSteps = $stepCards.length;
            WLGFMultiStep.goToStep($container, null, 1, totalSteps, true);
        }
    };

    window.WLGFMultiStep = WLGFMultiStep;

    // ==========================================
    // Visual Conditional Logic Engine (PRO-001)
    // ==========================================
    const WLGFConditionalLogic = {
        init: function() {
            $('.wlgf-container').each(function() {
                const $container = $(this);
                const $form = $container.find('form');
                const formId = $form.find('input[name="wlgf_form_id"]').val() || ($form.attr('id') ? $form.attr('id').replace('wlgf-form-', '') : '');
                const config = (window.WLGFShortcodeData && window.WLGFShortcodeData[formId]) ? window.WLGFShortcodeData[formId] : (window.WLGFShortcode || {});
                const conditions = config.Conditions;

                if (!conditions || typeof conditions !== 'object' || Array.isArray(conditions) || !Object.keys(conditions).length) {
                    return;
                }

                function evaluate() {
                    WLGFConditionalLogic.evaluateForm($form, conditions);
                }

                // Evaluate on initial load
                evaluate();

                // Evaluate in real-time on input/change
                $form.on('input change', 'input, select, textarea', function() {
                    evaluate();
                });
            });
        },

        getFieldValue: function($form, fieldName) {
            const $field = $form.find('[name="' + fieldName + '"], [name="' + fieldName + '[]"], [name^="' + fieldName + '["]');

            if (!$field.length) {
                return '';
            }

            const type = $field.attr('type');

            if (type === 'radio') {
                return $field.filter(':checked').val() || '';
            }

            if (type === 'checkbox') {
                if ($field.length > 1) {
                    const vals = [];
                    $field.filter(':checked').each(function() {
                        vals.push($(this).val());
                    });
                    return vals;
                } else {
                    return $field.is(':checked') ? ($field.val() || '1') : '';
                }
            }

            return $field.val() || '';
        },

        evaluateRule: function($form, rule) {
            const fieldVal = WLGFConditionalLogic.getFieldValue($form, rule.field);
            const targetVal = rule.value || '';
            const op = rule.operator || 'equals';

            const strFieldVal = Array.isArray(fieldVal) ? fieldVal.join(',') : String(fieldVal);
            const lowerFieldVal = strFieldVal.toLowerCase().trim();
            const lowerTargetVal = String(targetVal).toLowerCase().trim();

            switch (op) {
                case 'equals':
                    if (Array.isArray(fieldVal)) {
                        return fieldVal.some(function(v) { return String(v).toLowerCase().trim() === lowerTargetVal; });
                    }
                    return lowerFieldVal === lowerTargetVal;

                case 'not_equals':
                    if (Array.isArray(fieldVal)) {
                        return !fieldVal.some(function(v) { return String(v).toLowerCase().trim() === lowerTargetVal; });
                    }
                    return lowerFieldVal !== lowerTargetVal;

                case 'contains':
                    return lowerFieldVal.indexOf(lowerTargetVal) !== -1;

                case 'not_contains':
                    return lowerFieldVal.indexOf(lowerTargetVal) === -1;

                case 'greater_than':
                    return parseFloat(fieldVal) > parseFloat(targetVal);

                case 'less_than':
                    return parseFloat(fieldVal) < parseFloat(targetVal);

                case 'is_empty':
                    return fieldVal === '' || fieldVal === null || fieldVal === undefined || (Array.isArray(fieldVal) && fieldVal.length === 0);

                case 'is_not_empty':
                    return fieldVal !== '' && fieldVal !== null && fieldVal !== undefined && (!Array.isArray(fieldVal) || fieldVal.length > 0);

                default:
                    return false;
            }
        },

        evaluateForm: function($form, conditions) {
            Object.keys(conditions).forEach(function(targetFieldName) {
                const cond = conditions[targetFieldName];
                if (!cond || !Array.isArray(cond.rules) || !cond.rules.length) {
                    return;
                }

                const action = cond.action || 'show';
                const logic = cond.logic || 'all';

                let isMatch = (logic === 'all');

                for (let i = 0; i < cond.rules.length; i++) {
                    const ruleResult = WLGFConditionalLogic.evaluateRule($form, cond.rules[i]);

                    if (logic === 'all') {
                        if (!ruleResult) {
                            isMatch = false;
                            break;
                        }
                    } else if (logic === 'any') {
                        if (ruleResult) {
                            isMatch = true;
                            break;
                        }
                    }
                }

                const shouldShow = (action === 'show') ? isMatch : !isMatch;
                const $targetWrapper = $form.find('.wlgf-field-wrap[data-field-name="' + targetFieldName + '"]');

                if ($targetWrapper.length) {
                    const $inputs = $targetWrapper.find('input, select, textarea');

                    if (shouldShow) {
                        $targetWrapper.removeClass('wlgf-d-none');
                        $inputs.prop('disabled', false);

                        // Restore original required status if cached
                        $inputs.each(function() {
                            if ($(this).attr('data-wlgf-required') === '1') {
                                $(this).prop('required', true);
                            }
                        });
                    } else {
                        $targetWrapper.addClass('wlgf-d-none');
                        $inputs.prop('disabled', true);

                        // Cache and strip required attribute so browser doesn't block submission
                        $inputs.each(function() {
                            if ($(this).prop('required')) {
                                $(this).attr('data-wlgf-required', '1');
                                $(this).prop('required', false);
                            }
                        });
                    }
                }
            });
        }
    };

    /**
     * Drag & Drop Multi-File Upload Controller (PRO-003)
     */
    const WLGFDropzone = {
        init: function() {
            $('.wlgf-dropzone-wrap').each(function() {
                const $wrap = $(this);
                const $dropzone = $wrap.find('.wlgf-dropzone');
                const $input = $wrap.find('.wlgf-dropzone__input');
                const $fileList = $wrap.find('.wlgf-file-list');
                const maxSize = parseInt($wrap.data('max-size'), 10) || 52428800; // 50MB default
                const isMultiple = $input.prop('multiple');

                let dt = new DataTransfer();

                function updateFileList() {
                    $fileList.empty();
                    for (let i = 0; i < dt.files.length; i++) {
                        const file = dt.files[i];
                        const formattedSize = (file.size / 1024 < 1024) 
                            ? (file.size / 1024).toFixed(1) + ' KB' 
                            : (file.size / (1024 * 1024)).toFixed(1) + ' MB';
                        
                        const $chip = $('<div class="wlgf-file-chip"></div>')
                            .text(file.name + ' (' + formattedSize + ')')
                            .append($('<button type="button" class="wlgf-file-chip__remove" aria-label="Remove file">&times;</button>').data('index', i));
                        
                        $fileList.append($chip);
                    }
                    if ($input.length && $input[0]) {
                        $input[0].files = dt.files;
                    }
                }

                const allowedTypesRaw = $wrap.data('allowed-types');
                const allowedExts = allowedTypesRaw ? allowedTypesRaw.toString().toLowerCase().split(',').map(function(s) { return s.trim().replace(/^\./, ''); }).filter(Boolean) : [];

                function addFiles(files) {
                    let totalSize = 0;
                    for (let i = 0; i < dt.files.length; i++) {
                        totalSize += dt.files[i].size;
                    }

                    if (!isMultiple) {
                        dt = new DataTransfer();
                        totalSize = 0;
                    }

                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        const ext = (file.name.indexOf('.') !== -1) ? file.name.split('.').pop().toLowerCase() : '';
                        if (allowedExts.length > 0 && allowedExts.indexOf(ext) === -1) {
                            const typeMsg = 'Invalid file type ".' + ext + '". Accepted formats: ' + allowedExts.join(', ').toUpperCase();
                            if (typeof wlgfToast === 'function') {
                                wlgfToast(typeMsg, 'warning');
                            } else {
                                alert(typeMsg);
                            }
                            continue;
                        }
                        if (totalSize + file.size > maxSize) {
                            const maxMb = (maxSize / (1024 * 1024)).toFixed(0);
                            const sizeMsg = 'File size exceeds maximum allowed limit of ' + maxMb + 'MB.';
                            if (typeof wlgfToast === 'function') {
                                wlgfToast(sizeMsg, 'warning');
                            } else {
                                alert(sizeMsg);
                            }
                            break;
                        }
                        totalSize += file.size;
                        dt.items.add(file);
                        if (!isMultiple) break;
                    }
                    updateFileList();
                }

                $input.on('click', function(e) {
                    e.stopPropagation();
                });

                $dropzone.on('click', function(e) {
                    if (e.target === $input[0]) {
                        return;
                    }
                    $input.trigger('click');
                });

                $dropzone.on('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        $input.trigger('click');
                    }
                });

                $dropzone.on('dragover dragenter', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $dropzone.addClass('wlgf-dropzone--dragover');
                });

                $dropzone.on('dragleave dragend drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $dropzone.removeClass('wlgf-dropzone--dragover');
                });

                $dropzone.on('drop', function(e) {
                    const droppedFiles = e.originalEvent.dataTransfer.files;
                    if (droppedFiles.length) {
                        addFiles(droppedFiles);
                    }
                });

                $input.on('change', function() {
                    if (this.files.length) {
                        addFiles(this.files);
                    }
                });

                $fileList.on('click', '.wlgf-file-chip__remove', function(e) {
                    e.stopPropagation();
                    const removeIdx = $(this).data('index');
                    const newDt = new DataTransfer();
                    for (let i = 0; i < dt.files.length; i++) {
                        if (i !== removeIdx) {
                            newDt.items.add(dt.files[i]);
                        }
                    }
                    dt = newDt;
                    updateFileList();
                });
            });
        }
    };

    /**
     * Interactive Star Rating Controller (PRO-004)
     */
    const WLGFStarRating = {
        init: function() {
            $('.wlgf-star-rating').each(function() {
                const $wrap = $(this);
                const $stars = $wrap.find('.wlgf-star');
                const $radios = $wrap.find('.wlgf-star-radio');

                function updateStarVisuals(val) {
                    $stars.each(function() {
                        const starVal = parseInt($(this).data('value'), 10);
                        if (starVal <= val) {
                            $(this).addClass('wlgf-star--active').attr('aria-checked', starVal === val ? 'true' : 'false');
                        } else {
                            $(this).removeClass('wlgf-star--active').attr('aria-checked', 'false');
                        }
                    });
                }

                $stars.on('mouseenter', function() {
                    const hoverVal = parseInt($(this).data('value'), 10);
                    $stars.each(function() {
                        const starVal = parseInt($(this).data('value'), 10);
                        $(this).toggleClass('wlgf-star--hover', starVal <= hoverVal);
                    });
                });

                $wrap.on('mouseleave', function() {
                    $stars.removeClass('wlgf-star--hover');
                });

                $stars.on('click', function() {
                    const clickVal = parseInt($(this).data('value'), 10);
                    $radios.filter('[value="' + clickVal + '"]').prop('checked', true).trigger('change');
                    updateStarVisuals(clickVal);
                });

                $stars.on('keydown', function(e) {
                    const currentVal = parseInt($radios.filter(':checked').val(), 10) || 0;
                    if (e.key === 'ArrowRight' || e.key === 'ArrowUp') {
                        e.preventDefault();
                        const nextVal = Math.min(5, currentVal + 1);
                        $radios.filter('[value="' + nextVal + '"]').prop('checked', true).trigger('change');
                        updateStarVisuals(nextVal);
                        $stars.filter('[data-value="' + nextVal + '"]').focus();
                    } else if (e.key === 'ArrowLeft' || e.key === 'ArrowDown') {
                        e.preventDefault();
                        const prevVal = Math.max(1, currentVal - 1);
                        $radios.filter('[value="' + prevVal + '"]').prop('checked', true).trigger('change');
                        updateStarVisuals(prevVal);
                        $stars.filter('[data-value="' + prevVal + '"]').focus();
                    } else if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        $(this).trigger('click');
                    }
                });

                // Reset listener to clear star visuals when form resets
                $wrap.closest('form').on('reset', function() {
                    setTimeout(function() {
                        updateStarVisuals(0);
                        $radios.prop('checked', false);
                    }, 10);
                });

                // Init with checked value if any
                const initialVal = parseInt($radios.filter(':checked').val(), 10);
                if (initialVal) {
                    updateStarVisuals(initialVal);
                }
            });
        }
    };

    /**
     * Interactive NPS Score Matrix Controller (PRO-004)
     */
    const WLGFNPSMatrix = {
        init: function() {
            $('.wlgf-nps-matrix').each(function() {
                const $wrap = $(this);
                const $btns = $wrap.find('.wlgf-nps-btn');
                const $radios = $wrap.find('.wlgf-nps-radio');

                $btns.on('click', function() {
                    const score = $(this).data('value');
                    $btns.removeClass('wlgf-nps-btn--selected').attr('aria-checked', 'false');
                    $(this).addClass('wlgf-nps-btn--selected').attr('aria-checked', 'true');
                    $radios.filter('[value="' + score + '"]').prop('checked', true).trigger('change');
                });

                // Reset listener to clear NPS button selections when form resets
                $wrap.closest('form').on('reset', function() {
                    setTimeout(function() {
                        $btns.removeClass('wlgf-nps-btn--selected').attr('aria-checked', 'false');
                        $radios.prop('checked', false);
                    }, 10);
                });

                // Init with checked value if any
                const initialVal = $radios.filter(':checked').val();
                if (initialVal !== undefined && initialVal !== '') {
                    $btns.filter('[data-value="' + initialVal + '"]').addClass('wlgf-nps-btn--selected').attr('aria-checked', 'true');
                }
            });
        }
    };

    /**
     * Safe Mathematical Expression AST Parser & Dynamic Price Calculation Engine (PRO-015)
     */
    const WLGFCalculation = {
        init: function() {
            $('.wlgf-container').each(function() {
                const $container = $(this);
                const $form = $container.find('form');
                const $calcWraps = $form.find('.wlgf-calc-wrap');

                if (!$calcWraps.length) {
                    return;
                }

                function recalculate() {
                    $calcWraps.each(function() {
                        const $wrap = $(this);
                        const rawFormula = $wrap.data('formula') || '';
                        const decimals = parseInt($wrap.data('decimals'), 10) || 2;
                        const fieldName = $wrap.data('field-name');

                        const val = WLGFCalculation.evaluateFormula($form, rawFormula);
                        const formatted = val.toFixed(decimals);

                        $wrap.find('.wlgf-calc-val').text(formatted);
                        $wrap.find('.wlgf-calc-hidden-input').val(formatted);

                        // If payment checkout is dynamic and linked to this calculation field, update it
                        const $checkout = $form.find('.wlgf-payment-checkout-wrap');
                        if ($checkout.length && $checkout.data('pay-type') === 'calculation') {
                            const linkedCalc = $checkout.data('pay-calc');
                            if (!linkedCalc || linkedCalc === fieldName) {
                                $checkout.find('.wlgf-pay-amt-val').text(formatted);
                            }
                        }
                    });
                }

                // Initial calculation run
                recalculate();

                // Live event listener on any form field change
                $form.on('input change keyup', 'input, select, textarea', function() {
                    recalculate();
                });
            });
        },

        getNumericValue: function($form, fieldName) {
            const $field = $form.find('[name="' + fieldName + '"], [name="' + fieldName + '[]"], [name^="' + fieldName + '["]');
            if (!$field.length) {
                return 0;
            }

            const type = $field.attr('type');

            if (type === 'radio') {
                const checkedVal = $field.filter(':checked').val();
                return parseFloat(checkedVal) || 0;
            }

            if (type === 'checkbox') {
                let sum = 0;
                $field.filter(':checked').each(function() {
                    sum += parseFloat($(this).val()) || 0;
                });
                return sum;
            }

            const raw = $field.val();
            return parseFloat(raw) || 0;
        },

        evaluateFormula: function($form, formula) {
            if (!formula || typeof formula !== 'string') {
                return 0;
            }

            // Replace bracketed field tokens e.g. {quantity} or [quantity] with their live numeric value
            let expr = formula.replace(/[\{\[]([^\}\]]+)[\}\]]/g, function(match, token) {
                return String(WLGFCalculation.getNumericValue($form, token.trim()));
            });

            // Safe Tokenized Math Evaluator without eval() or Function()
            try {
                return WLGFCalculation.safeEvaluate(expr);
            } catch (err) {
                return 0;
            }
        },

        // Recursive Descent Mathematical Expression Parser (Strict AST)
        safeEvaluate: function(expr) {
            let tokens = expr.replace(/\s+/g, '');
            if (!tokens.length) {
                return 0;
            }

            let pos = 0;

            function parseExpression() {
                let val = parseTerm();
                while (pos < tokens.length) {
                    let op = tokens[pos];
                    if (op === '+' || op === '-') {
                        pos++;
                        let nextVal = parseTerm();
                        val = (op === '+') ? (val + nextVal) : (val - nextVal);
                    } else {
                        break;
                    }
                }
                return val;
            }

            function parseTerm() {
                let val = parseFactor();
                while (pos < tokens.length) {
                    let op = tokens[pos];
                    if (op === '*' || op === '/') {
                        pos++;
                        let nextVal = parseFactor();
                        val = (op === '*') ? (val * nextVal) : (nextVal !== 0 ? val / nextVal : 0);
                    } else {
                        break;
                    }
                }
                return val;
            }

            function parseFactor() {
                if (pos >= tokens.length) {
                    return 0;
                }

                // Check for unary operators
                if (tokens[pos] === '-') {
                    pos++;
                    return -parseFactor();
                }

                if (tokens[pos] === '+') {
                    pos++;
                    return parseFactor();
                }

                // Parentheses
                if (tokens[pos] === '(') {
                    pos++; // consume '('
                    let val = parseExpression();
                    if (pos < tokens.length && tokens[pos] === ')') {
                        pos++; // consume ')'
                    }
                    return val;
                }

                // Number (digits and optional decimal point)
                let start = pos;
                while (pos < tokens.length && ((tokens[pos] >= '0' && tokens[pos] <= '9') || tokens[pos] === '.')) {
                    pos++;
                }

                if (pos > start) {
                    let numStr = tokens.substring(start, pos);
                    return parseFloat(numStr) || 0;
                }

                pos++;
                return 0;
            }

            return parseExpression() || 0;
        }
    };

    /**
     * Seamless Stripe & PayPal Gateway Integration (PRO-016)
     */
    const WLGFPayment = {
        init: function() {
            $('.wlgf-container').each(function() {
                const $container = $(this);
                const $form = $container.find('form');
                const formId = $form.find('input[name="wlgf_form_id"]').val() || ($form.attr('id') ? $form.attr('id').replace('wlgf-form-', '') : '');
                const config = (window.WLGFShortcodeData && window.WLGFShortcodeData[formId]) ? window.WLGFShortcodeData[formId] : (window.WLGFShortcode || {});

                const $checkoutWrap = $form.find('.wlgf-payment-checkout-wrap');
                if (!$checkoutWrap.length) {
                    return;
                }

                const isPaymentEnabled = config.PaymentEnabled || Boolean(config.StripePubKey || config.PayPalClientId || $checkoutWrap.length);
                if (!isPaymentEnabled || !formId) {
                    return;
                }

                let stripe = null;
                let cardElement = null;

                // 1. Stripe Elements Setup
                const $stripeElem = $('#wlgf-stripe-card-element-' + formId);
                const initStripeElements = function() {
                    if ($stripeElem.length && config.StripePubKey && typeof Stripe !== 'undefined') {
                        if ($stripeElem.children().length > 0) return;
                        try {
                            stripe = Stripe(config.StripePubKey);
                            const elements = stripe.elements();
                            cardElement = elements.create('card', {
                                style: {
                                    base: {
                                        color: '#1e293b',
                                        fontFamily: 'inherit',
                                        fontSize: '15px',
                                        '::placeholder': {
                                            color: '#94a3b8'
                                        }
                                    },
                                    invalid: {
                                        color: '#ef4444',
                                        iconColor: '#ef4444'
                                    }
                                }
                            });
                            cardElement.mount('#wlgf-stripe-card-element-' + formId);

                            cardElement.on('change', function(event) {
                                const $displayError = $('#wlgf-stripe-errors-' + formId);
                                if (event.error) {
                                    $displayError.text(event.error.message);
                                } else {
                                    $displayError.text('');
                                }
                            });
                        } catch (e) {
                            console.warn('WLGF Stripe Init Error:', e);
                        }
                    }
                };

                if ($stripeElem.length && config.StripePubKey) {
                    if (typeof Stripe !== 'undefined') {
                        initStripeElements();
                    } else if (!document.querySelector('script[src*="js.stripe.com"]')) {
                        const stripeScript = document.createElement('script');
                        stripeScript.src = 'https://js.stripe.com/v3/';
                        stripeScript.onload = function() {
                            initStripeElements();
                        };
                        document.head.appendChild(stripeScript);
                    } else {
                        let stripeWait = 0;
                        const stripeInterval = setInterval(function() {
                            stripeWait++;
                            if (typeof Stripe !== 'undefined' || stripeWait > 30) {
                                clearInterval(stripeInterval);
                                if (typeof Stripe !== 'undefined') initStripeElements();
                            }
                        }, 100);
                    }
                }

                // 2. PayPal Buttons Setup
                const $paypalElem = $('#wlgf-paypal-button-' + formId);
                if ($paypalElem.length && config.PayPalClientId) {
                    let ppAttempts = 0;
                    const maxAttempts = 30; // 30 * 200ms = 6s
                    const renderPayPalButtons = function() {
                        if (typeof paypal !== 'undefined' && paypal.Buttons) {
                            try {
                                $paypalElem.empty();
                                paypal.Buttons({
                                    onClick: function(data, actions) {
                                        // Validate required fields in the form before opening PayPal
                                        let isValid = true;
                                        let $firstInvalid = null;

                                        $form.find('input, textarea, select').each(function() {
                                            const $field = $(this);
                                            // Skip hidden/disabled inputs and internal hidden payment fields
                                            if (!$field.is(':visible') || $field.is(':disabled') || $field.attr('type') === 'hidden') return;
                                            
                                            if ($field.prop('required')) {
                                                const val = $.trim($field.val() || '');
                                                if (!val) {
                                                    isValid = false;
                                                    $field.addClass('wlgf-error').trigger('wlgf-validate');
                                                    if (!$firstInvalid) $firstInvalid = $field;
                                                }
                                            }
                                        });

                                        if (!isValid) {
                                            if (typeof wlgfToast === 'function') {
                                                wlgfToast('Please fill all required fields before proceeding to payment.', 'warning');
                                            } else {
                                                alert('Please fill all required fields before proceeding to payment.');
                                            }
                                            if ($firstInvalid) {
                                                $firstInvalid.focus();
                                                $('html, body').animate({ scrollTop: Math.max(0, $firstInvalid.offset().top - 100) }, 300);
                                            }
                                            return actions.reject();
                                        }
                                        return actions.resolve();
                                    },
                                    createOrder: function(data, actions) {
                                        const payType = $checkoutWrap.data('pay-type');
                                        let amount = 0;
                                        if (payType === 'fixed') {
                                            amount = parseFloat($checkoutWrap.data('pay-amount')) || 0;
                                        } else {
                                            amount = parseFloat($checkoutWrap.find('.wlgf-pay-amt-val').text()) || 0;
                                        }
                                        if (amount <= 0) {
                                            if (typeof wlgfToast === 'function') {
                                                wlgfToast('Payment amount must be greater than zero.', 'warning');
                                            } else {
                                                alert('Payment amount must be greater than zero.');
                                            }
                                            return false;
                                        }

                                        return actions.order.create({
                                            purchase_units: [{
                                                amount: {
                                                    currency_code: config.PaymentCurrency || 'USD',
                                                    value: amount.toFixed(2)
                                                }
                                            }]
                                        });
                                    },
                                    onApprove: function(data, actions) {
                                        if (typeof wlgfToast === 'function') {
                                            wlgfToast('Payment authorized! Completing order...', 'info', 4000);
                                        }

                                        return actions.order.capture().then(function(details) {
                                            if (!details || (details.status !== 'COMPLETED' && details.status !== 'APPROVED')) {
                                                if (typeof wlgfToast === 'function') {
                                                    wlgfToast('PayPal payment was not completed.', 'error');
                                                } else {
                                                    alert('PayPal payment was not completed.');
                                                }
                                                return;
                                            }
                                            $form.find('#wlgf_payment_transaction_id').val(details.id);
                                            $form.find('#wlgf_payment_status').val('completed');
                                            $form.find('#wlgf_payment_amount_paid').val(details.purchase_units[0].amount.value);
                                            $form.find('#wlgf_payment_gateway').val('paypal');

                                            // Submit form now
                                            $form.trigger('submit');
                                        }).catch(function(err) {
                                            console.error('[WLGF Payment Debug] Capture Error:', err);
                                        });
                                    },
                                    onError: function(err) {
                                        console.error('[WLGF Payment Debug] PayPal Checkout Error:', err);
                                    }
                                }).render('#wlgf-paypal-button-' + formId);
                            } catch (e) {
                                console.warn('[WLGF Payment Debug] PayPal Buttons Init Error:', e);
                            }
                        } else {
                            ppAttempts++;
                            if (ppAttempts < maxAttempts) {
                                setTimeout(renderPayPalButtons, 200);
                            } else {
                                console.warn('[WLGF Payment Debug] PayPal SDK script timed out after 6 seconds.');
                            }
                        }
                    };
                    if (typeof paypal === 'undefined' && !document.querySelector('script[src*="paypal.com/sdk"]')) {
                        const ppScript = document.createElement('script');
                        const ppCurrency = config.PaymentCurrency || 'USD';
                        ppScript.src = 'https://www.paypal.com/sdk/js?client-id=' + encodeURIComponent(config.PayPalClientId) + '&currency=' + encodeURIComponent(ppCurrency);
                        ppScript.onload = function() {
                            renderPayPalButtons();
                        };
                        document.head.appendChild(ppScript);
                    } else {
                        renderPayPalButtons();
                    }
                }

                // 3. Smart Gateway Switching & Submit Button Management
                const $methodRadios = $form.find('input[type="radio"][name*="Payment_Method"], input[type="radio"][name*="payment_method"]');
                const $gatewayTabs = $form.find('.wlgf-gateway-tab');
                const updateGatewayVisibility = function(forcedGateway) {
                    let selectedMethod = 'stripe';
                    if (forcedGateway) {
                        selectedMethod = forcedGateway;
                    } else if ($methodRadios.filter(':checked').length) {
                        const checkedVal = ($methodRadios.filter(':checked').val() || '').toLowerCase();
                        if (checkedVal.indexOf('paypal') !== -1) {
                            selectedMethod = 'paypal';
                        } else if (checkedVal.indexOf('stripe') !== -1 || checkedVal.indexOf('card') !== -1) {
                            selectedMethod = 'stripe';
                        }
                    } else if ($gatewayTabs.filter('.active').length) {
                        selectedMethod = $gatewayTabs.filter('.active').data('target') || 'stripe';
                    } else if (stripe && cardElement) {
                        selectedMethod = 'stripe';
                    } else if ($paypalElem.length && config.PayPalClientId) {
                        selectedMethod = 'paypal';
                    }

                    // Sync gateway tabs state
                    if ($gatewayTabs.length) {
                        $gatewayTabs.removeClass('active');
                        $gatewayTabs.filter('[data-target="' + selectedMethod + '"]').addClass('active');
                    }

                    // Sync radio state if not matching
                    if ($methodRadios.length) {
                        $methodRadios.each(function() {
                            const val = ($(this).val() || '').toLowerCase();
                            if ((selectedMethod === 'paypal' && val.indexOf('paypal') !== -1) ||
                                (selectedMethod === 'stripe' && (val.indexOf('stripe') !== -1 || val.indexOf('card') !== -1))) {
                                $(this).prop('checked', true);
                            }
                        });
                    }

                    const $submitBtnWrap = $form.find('.wlgf-button-wrap');
                    if (selectedMethod.indexOf('paypal') !== -1) {
                        $form.find('.wlgf-stripe-option').hide();
                        $form.find('.wlgf-paypal-option').show();
                        if ($paypalElem.length && config.PayPalClientId) {
                            $submitBtnWrap.hide();
                        }
                    } else {
                        $form.find('.wlgf-stripe-option').show();
                        $form.find('.wlgf-paypal-option').hide();
                        $submitBtnWrap.show();
                    }
                };

                $gatewayTabs.on('click', function(e) {
                    e.preventDefault();
                    const target = $(this).data('target');
                    updateGatewayVisibility(target);
                });

                if ($methodRadios.length) {
                    $methodRadios.on('change', function() {
                        const val = ($(this).val() || '').toLowerCase();
                        const target = (val.indexOf('paypal') !== -1) ? 'paypal' : 'stripe';
                        updateGatewayVisibility(target);
                    });
                }
                updateGatewayVisibility();

                // 4. Form Submit Interceptor for Payments
                $form.on('submit', function(e) {
                    if ($form.find('#wlgf_payment_transaction_id').val()) {
                        return true;
                    }

                    if (stripe && cardElement) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        const $submitBtn = $form.find('button[type="submit"], input[type="submit"]');
                        const $errorEl = $('#wlgf-stripe-errors-' + formId);
                        $errorEl.empty();

                        // Pre-validate visible required fields before charging the card
                        let isValid = true;
                        let $firstInvalid = null;
                        $form.find('input, textarea, select').each(function() {
                            const $field = $(this);
                            if (!$field.is(':visible') || $field.is(':disabled') || $field.attr('type') === 'hidden') return;
                            if ($field.prop('required')) {
                                const val = $.trim($field.val() || '');
                                if (!val) {
                                    isValid = false;
                                    $field.addClass('wlgf-error').trigger('wlgf-validate');
                                    if (!$firstInvalid) $firstInvalid = $field;
                                }
                            }
                        });

                        if (!isValid) {
                            if (typeof wlgfToast === 'function') {
                                wlgfToast('Please fill all required fields before proceeding to payment.', 'warning');
                            } else {
                                alert('Please fill all required fields before proceeding to payment.');
                            }
                            if ($firstInvalid) {
                                $firstInvalid.focus();
                                $('html, body').animate({ scrollTop: Math.max(0, $firstInvalid.offset().top - 100) }, 300);
                            }
                            return false;
                        }

                        $submitBtn.prop('disabled', true);

                        const payType = $checkoutWrap.data('pay-type');
                        let amount = 0;
                        if (payType === 'fixed') {
                            amount = parseFloat($checkoutWrap.data('pay-amount')) || 0;
                        } else {
                            amount = parseFloat($checkoutWrap.find('.wlgf-pay-amt-val').text()) || 0;
                        }

                        if (amount <= 0) {
                            $errorEl.text('Payment amount must be greater than zero.');
                            $submitBtn.prop('disabled', false);
                            return false;
                        }

                        const formNonce = $form.find('input[name="wlgf_form_nonce"]').val() || '';

                        // 1. Create PaymentIntent on server
                        $.ajax({
                            url: config.ajaxUrl || (window.WLGFShortcode && window.WLGFShortcode.ajaxUrl) || '/wp-admin/admin-ajax.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                action: 'wlgf_create_stripe_payment_intent',
                                form_id: formId,
                                amount: amount,
                                nonce: formNonce
                            },
                            success: function(res) {
                                if (!res || !res.success || !res.data || !res.data.client_secret) {
                                    const errMsg = (res && res.data && res.data.message) ? res.data.message : 'Unable to initialize payment. Please try again.';
                                    $errorEl.text(errMsg);
                                    $submitBtn.prop('disabled', false);
                                    return;
                                }

                                // Extract customer billing details for Indian export regulation compliance
                                const customerName = $.trim($form.find('input[name*="name"], input[placeholder*="Name"], input[data-field-type="name"]').filter(':visible').first().val()) || 'Customer';
                                const customerEmail = $.trim($form.find('input[type="email"]').filter(':visible').first().val()) || '';
                                const customerPhone = $.trim($form.find('input[type="tel"]').filter(':visible').first().val()) || '';

                                const formLine1 = $.trim($form.find('input[name*="address"], input[name*="street"], input[name*="line1"]').filter(':visible').first().val());
                                const formCity = $.trim($form.find('input[name*="city"]').filter(':visible').first().val());
                                const formState = $.trim($form.find('input[name*="state"], input[name*="province"]').filter(':visible').first().val());
                                const formZip = $.trim($form.find('input[name*="zip"], input[name*="postal"], input[name*="pincode"]').filter(':visible').first().val());
                                const formCountry = $.trim($form.find('select[name*="country"], input[name*="country"]').filter(':visible').first().val());

                                const isNonInr = (config.PaymentCurrency || 'USD').toUpperCase() !== 'INR';

                                const billingAddress = {
                                    line1: formLine1 || '123 Market Street',
                                    city: formCity || (isNonInr ? 'New York' : 'Mumbai'),
                                    state: formState || (isNonInr ? 'NY' : 'MH'),
                                    postal_code: formZip || (isNonInr ? '10001' : '400001'),
                                    country: formCountry || (isNonInr ? 'US' : 'IN')
                                };

                                const paymentMethodData = {
                                    card: cardElement,
                                    billing_details: {
                                        name: customerName,
                                        address: billingAddress
                                    }
                                };
                                if (customerEmail) {
                                    paymentMethodData.billing_details.email = customerEmail;
                                }
                                if (customerPhone) {
                                    paymentMethodData.billing_details.phone = customerPhone;
                                }

                                if (typeof wlgfToast === 'function') {
                                    wlgfToast('Processing card payment...', 'info', 4000);
                                }

                                // 2. Confirm Card Payment via Stripe.js
                                stripe.confirmCardPayment(res.data.client_secret, {
                                    payment_method: paymentMethodData
                                }).then(function(result) {
                                    if (result.error) {
                                        $errorEl.text(result.error.message);
                                        $submitBtn.prop('disabled', false);
                                    } else if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                                        $form.find('#wlgf_payment_transaction_id').val(result.paymentIntent.id);
                                        $form.find('#wlgf_payment_status').val('completed');
                                        $form.find('#wlgf_payment_amount_paid').val(amount.toFixed(2));
                                        $form.find('#wlgf_payment_gateway').val('stripe');
                                        $submitBtn.prop('disabled', false);
                                        $form.trigger('submit');
                                    } else {
                                        $errorEl.text('Payment was not completed.');
                                        $submitBtn.prop('disabled', false);
                                    }
                                });
                            },
                            error: function() {
                                $errorEl.text('Payment processing failed. Please check your connection.');
                                $submitBtn.prop('disabled', false);
                            }
                        });

                        return false;
                    }

                    // If PayPal is available on the form, remind user to click PayPal
                    if ($paypalElem.length && config.PayPalClientId) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        if (typeof wlgfAlert === 'function') {
                            wlgfAlert({
                                title: 'Payment Required',
                                message: 'Please complete payment via the PayPal button above to submit this form.',
                                type: 'warning'
                            });
                        } else {
                            alert('Please complete payment via the PayPal button above to submit this form.');
                        }
                        return false;
                    }

                    return true;
                });
            });
        }
    };

    /**
     * Non-blocking Form Impression & Step Progression Analytics Beacon (PRO-017)
     */
    const WLGFAnalytics = {
        init: function() {
            function getDeviceType() {
                const width = window.innerWidth;
                if (width < 768) return 'mobile';
                if (width < 1024) return 'tablet';
                return 'desktop';
            }

            $('.wlgf-container').each(function() {
                const $container = $(this);
                const $form = $container.find('form');
                const formId = $form.find('input[name="wlgf_form_id"]').val() || ($form.attr('id') ? $form.attr('id').replace('wlgf-form-', '') : '');
                const config = (window.WLGFShortcodeData && window.WLGFShortcodeData[formId]) ? window.WLGFShortcodeData[formId] : (window.WLGFShortcode || {});
                const ajaxUrl = config.ajaxUrl;

                if (!formId || !ajaxUrl) {
                    return;
                }

                let trackedImpression = false;

                function track(step) {
                    const data = {
                        action: 'wlgf_track_analytics',
                        form_id: formId,
                        step: step || 1,
                        device: getDeviceType()
                    };

                    if (navigator.sendBeacon) {
                        const formData = new FormData();
                        formData.append('action', 'wlgf_track_analytics');
                        formData.append('form_id', formId);
                        formData.append('step', step || 1);
                        formData.append('device', getDeviceType());
                        navigator.sendBeacon(ajaxUrl, formData);
                    } else {
                        $.post(ajaxUrl, data);
                    }
                }

                if ($form.length) {
                    // Viewport entry impression tracking
                    if ('IntersectionObserver' in window) {
                        const observer = new IntersectionObserver(function(entries) {
                            if (entries[0].isIntersecting && !trackedImpression) {
                                trackedImpression = true;
                                track(1);
                                observer.disconnect();
                            }
                        }, { threshold: 0.15 });
                        observer.observe($form[0]);
                    } else {
                        trackedImpression = true;
                        track(1);
                    }

                    // Step Progression tracking for Multi-Step wizard
                    $form.on('click', '.wlgf-btn--next', function() {
                        const $card = $(this).closest('.wlgf-step-card');
                        const nextStep = parseInt($card.data('step'), 10) + 1;
                        track(nextStep);
                    });
                }
            });
        }
    };

    $(document).ready(function() {
        // Multi-Step Engine Initialization
        WLGFMultiStep.init();

        // Conditional Logic Engine Initialization
        WLGFConditionalLogic.init();

        // Dropzone File Upload Initialization
        WLGFDropzone.init();

        // Star Rating Initialization
        WLGFStarRating.init();

        // NPS Matrix Initialization
        WLGFNPSMatrix.init();

        // Calculation & Price Estimator Engine (PRO-015)
        WLGFCalculation.init();

        // Payment Gateway Integration (PRO-016)
        WLGFPayment.init();

        // Visual Analytics Beacon (PRO-017)
        WLGFAnalytics.init();

        // Abandoned & Partial Lead Capture Engine (Pillar 4)
        WLGFAbandoned.init();



        // Auto fadeout success message (configurable 5 to 50 seconds, default: 10s)
        if ($('#wlgf-msg').length) {
            let fadeoutSec = parseInt($('#wlgf-msg').data('fadeout'), 10);
            if (isNaN(fadeoutSec)) {
                fadeoutSec = parseInt((window.WLGFAjax && window.WLGFAjax.FadeoutSeconds) || (window.WLGFShortcode && window.WLGFShortcode.FadeoutSeconds) || 10, 10);
            }
            const fadeoutMs = Math.max(5, Math.min(50, isNaN(fadeoutSec) ? 10 : fadeoutSec)) * 1000;
            setTimeout(function() {
                $('#wlgf-msg').fadeOut(600);
            }, fadeoutMs);
        }
    });

    // ==========================================
    // Abandoned & Partial Lead Tracker (Pillar 4)
    // ==========================================
    const WLGFAbandoned = {
        timers: {},
        draftTokens: {},

        init: function() {
            $('form[id^="wlgf-form-"]').each(function() {
                const $form = $(this);
                const formId = $form.find('input[name="wlgf_form_id"]').val();
                if (!formId) return;

                // Load or generate draft token
                let storageKey = 'wlgf_draft_' + formId;
                let draftToken = sessionStorage.getItem(storageKey);
                if (!draftToken) {
                    draftToken = 'd_' + Math.random().toString(36).substring(2, 11) + Date.now().toString(36);
                    sessionStorage.setItem(storageKey, draftToken);
                }
                WLGFAbandoned.draftTokens[formId] = draftToken;

                // Append hidden draft token input to form if not present
                if (!$form.find('input[name="wlgf_draft_token"]').length) {
                    $form.append('<input type="hidden" name="wlgf_draft_token" value="' + draftToken + '">');
                }

                // Debounced capture on input/blur
                $form.on('input change blur', 'input:not([type="hidden"]):not([type="password"]), select, textarea', function() {
                    WLGFAbandoned.scheduleCapture($form, formId);
                });

                // Also trigger when Step Next button is clicked
                $form.on('click', '.wlgf-btn--next', function() {
                    WLGFAbandoned.sendCapture($form, formId);
                });

                // On form submit, clear draft token from storage
                $form.on('submit', function() {
                    sessionStorage.removeItem(storageKey);
                });
            });
        },

        scheduleCapture: function($form, formId) {
            clearTimeout(WLGFAbandoned.timers[formId]);
            WLGFAbandoned.timers[formId] = setTimeout(function() {
                WLGFAbandoned.sendCapture($form, formId);
            }, 1200);
        },

        sendCapture: function($form, formId) {
            const draftToken = WLGFAbandoned.draftTokens[formId];
            if (!draftToken) return;

            // Collect filled field values
            const fields = {};
            let hasContact = false;

            $form.find('input, select, textarea').each(function() {
                const name = $(this).attr('name');
                if (!name || name.indexOf('wlgf_') === 0 || name === 'recaptcha_response') return;
                
                const val = $(this).val();
                if (val && String(val).trim()) {
                    fields[name] = val;
                    if (name.toLowerCase().indexOf('email') !== -1 || (name.toLowerCase().indexOf('phone') !== -1 && String(val).length >= 6)) {
                        hasContact = true;
                    }
                }
            });

            if (!hasContact) return; // Only capture if at least email or phone is present

            const nonce = $form.find('input[name="wlgf_form_nonce"]').val();
            const ajaxUrl = (typeof WLGFAjax !== 'undefined' && WLGFAjax.ajaxUrl) ? WLGFAjax.ajaxUrl : (typeof ajax_object !== 'undefined' && ajax_object.ajax_url ? ajax_object.ajax_url : '/wp-admin/admin-ajax.php');

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wlgf_capture_abandoned_lead',
                    nonce: nonce,
                    form_id: formId,
                    draft_token: draftToken,
                    fields: fields
                },
                success: function(resp) {
                    if (resp && resp.data && resp.data.draft_token) {
                        WLGFAbandoned.draftTokens[formId] = resp.data.draft_token;
                        sessionStorage.setItem('wlgf_draft_' + formId, resp.data.draft_token);
                    }
                }
            });
        }
    };

    // Range slider dynamic live display
    document.addEventListener('DOMContentLoaded', function() {
        const rangeInputs = document.querySelectorAll('.wlgf-container input[type="range"]');
        rangeInputs.forEach(function(rangeInput) {
            const display = document.getElementById('range-value-display-' + rangeInput.id);
            if (display) {
                display.textContent = rangeInput.value;
            }
            rangeInput.addEventListener('input', function() {
                if (display) {
                    display.textContent = this.value;
                }
            });
        });
    });

    // Modern Flatpickr Datepicker & Tooltips on Frontend Forms
    $(function() {
        // Ensure calendar icon on date inputs
        $('.wlgf-container input[type="date"]').each(function() {
            if (!$(this).parent().hasClass('wlgf-date-input-wrap')) {
                $(this).wrap('<div class="wlgf-date-input-wrap"></div>');
                $(this).after('<span class="wlgf-date-icon" role="button" tabindex="-1" aria-label="Choose date"><i class="fa-regular fa-calendar-days"></i></span>');
            }
        });

        // Click calendar icon to open datepicker
        $(document).on('click', '.wlgf-container .wlgf-date-icon', function(e) {
            e.preventDefault();
            const $wrap = $(this).closest('.wlgf-date-input-wrap');
            const $input = $wrap.find('input');
            if ($input.length) {
                if ($input[0]._flatpickr) {
                    $input[0]._flatpickr.open();
                } else {
                    $input.trigger('focus').trigger('click');
                }
            }
        });

        if (typeof flatpickr !== 'undefined') {
            $('.wlgf-container input[type="date"]').each(function() {
                if (!this._flatpickr) {
                    const minAttr = $(this).attr('min');
                    const maxAttr = $(this).attr('max');
                    flatpickr(this, {
                        dateFormat: 'Y-m-d',
                        disableMobile: true,
                        allowInput: true,
                        minDate: minAttr || 'today',
                        maxDate: maxAttr || null
                    });
                }
            });
        }

        $('.wlgf-container .wlgf-tooltip-icon, .wlgf-container [data-bs-toggle="tooltip"]').each(function() {
            const $this = $(this);
            const title = $this.attr('title');
            if (title) {
                $this.attr('data-wlgf-tooltip', title).removeAttr('title');
            }
        });
    });

    // Prevent form resubmission on browser back navigation
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // Execute Google reCAPTCHA v3 if enabled
    if (typeof WLGFShortcode !== 'undefined' && WLGFShortcode.Recaptcha == 1 && WLGFShortcode.SiteKey) {
        function runRecaptcha() {
            if (typeof grecaptcha === 'undefined') {
                setTimeout(runRecaptcha, 150);
            } else {
                grecaptcha.ready(function() {
                    grecaptcha.execute(WLGFShortcode.SiteKey, { action: 'submit' }).then(function(token) {
                        const responseField = document.getElementById('recaptchaResponse');
                        if (responseField) {
                            responseField.value = token;
                        }
                    });
                });
            }
        }
        runRecaptcha();
    }

})(jQuery);