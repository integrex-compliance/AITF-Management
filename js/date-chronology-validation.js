/**
 * Date Chronology Validation Library
 * 
 * Provides real-time chronological date validation across all forms/modals
 * that feed into the Date Graph. Fetches the chronological context from the
 * server and validates dates on change/blur, blocking form submit if errors.
 *
 * Usage:
 *   // When modal opens, load context and attach validation:
 *   DateChronologyValidator.init({
 *       customerId: '123',
 *       typedServiceId: '456',
 *       serviceType: 'CA',   // optional, auto-detected if omitted
 *       fields: {
 *           'application_date':    { input: '.applicationDate',    errorDiv: '#appDateChronoError',    errorMsg: '#appDateChronoErrorMsg' },
 *           'application_rcv_date': { input: '.applicationRcvDate', errorDiv: '#rcvDateChronoError',    errorMsg: '#rcvDateChronoErrorMsg' },
 *           'contract_review_date': { input: '.review_date',       errorDiv: '#revDateChronoError',    errorMsg: '#revDateChronoErrorMsg' },
 *       },
 *       submitBtn: '.addApplication',
 *       formSelector: '#auditApplication form'
 *   });
 */
window.DateChronologyValidator = (function () {
    'use strict';

    var _context = null;    // { dates: {}, rules: [], service_type: '' }
    var _fields = {};       // field key -> { input, errorDiv, errorMsg }
    var _submitBtn = null;
    var _formSelector = null;
    var _isAttached = false;
    var _onSubmitHandler = null;

    /**
     * Parse a date string (DD.MM.YYYY or DD/MM/YYYY or YYYY-MM-DD) to a Date object.
     */
    function parseDate(dateStr) {
        if (!dateStr || dateStr.trim() === '') return null;
        dateStr = dateStr.trim();
        
        // Handle comma-separated multiple dates
        if (dateStr.indexOf(',') !== -1) {
            var parts = dateStr.split(',');
            var maxDate = null;
            for (var i = 0; i < parts.length; i++) {
                var d = parseDate(parts[i]); // Recursive call for each part
                if (d) {
                    if (!maxDate || d > maxDate) {
                        maxDate = d;
                    }
                }
            }
            return maxDate;
        }

        // DD.MM.YYYY
        var match = dateStr.match(/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/);
        if (match) {
            return new Date(parseInt(match[3]), parseInt(match[2]) - 1, parseInt(match[1]));
        }

        // DD/MM/YYYY
        match = dateStr.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
        if (match) {
            return new Date(parseInt(match[3]), parseInt(match[2]) - 1, parseInt(match[1]));
        }

        // YYYY-MM-DD (ISO)
        match = dateStr.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
        if (match) {
            return new Date(parseInt(match[1]), parseInt(match[2]) - 1, parseInt(match[3]));
        }

        // Fallback
        var d = new Date(dateStr);
        return isNaN(d.getTime()) ? null : d;
    }

    /**
     * Add months to a date.
     */
    function addMonths(date, months) {
        var d = new Date(date.getTime());
        d.setMonth(d.getMonth() + months);
        return d;
    }

    /**
     * Format Date to DD.MM.YYYY for display.
     */
    function formatDate(date) {
        if (!date) return '';
        var dd = String(date.getDate()).padStart(2, '0');
        var mm = String(date.getMonth() + 1).padStart(2, '0');
        var yyyy = date.getFullYear();
        return dd + '.' + mm + '.' + yyyy;
    }

    /**
     * Get the effective date for a field key. If the field is being edited in the
     * current form, use the form value. Otherwise use the context value.
     */
    function getEffectiveDate($inputElement) {
        if ($inputElement && $inputElement.length && $inputElement.val() && $inputElement.val().trim() !== '') {
            return parseDate($inputElement.val());
        }
        return null;
    }

    /**
     * Validate a single field against the chronological rules, optionally for a specific input element.
     * Returns { valid: true/false, errors: [string] }
     */
    function validateField(fieldKey, specificInput) {
        if (!_context || !_context.rules) return { valid: true, errors: [] };

        var errors = [];
        var fieldDate = null;
        
        if (specificInput) {
            fieldDate = getEffectiveDate($(specificInput));
        } else if (_fields[fieldKey]) {
            // For general validation without a specific element, just grab the first one
            fieldDate = getEffectiveDate($(_fields[fieldKey].input).first());
        }
        
        if (!fieldDate && _context && _context.dates && _context.dates[fieldKey]) {
            fieldDate = parseDate(_context.dates[fieldKey]);
        }

        // If this field has no date, skip validation (it's missing/empty)
        if (!fieldDate) return { valid: true, errors: [] };

        // Find all rules that apply to this field
        for (var i = 0; i < _context.rules.length; i++) {
            var rule = _context.rules[i];
            
            if (rule.field === fieldKey) {
                // We are editing the LATER date
                var afterDate = null;
                if (_fields[rule.must_be_after]) {
                    afterDate = getEffectiveDate($(_fields[rule.must_be_after].input).first());
                }
                if (!afterDate && _context && _context.dates && _context.dates[rule.must_be_after]) {
                    afterDate = parseDate(_context.dates[rule.must_be_after]);
                }
                
                if (!afterDate) continue; // Can't validate if the reference date is missing

                var compareDate = afterDate;
                // Handle offset (e.g., QMS + 2 months)
                if (rule.offset_months) {
                    compareDate = addMonths(afterDate, rule.offset_months);
                }

                var isViolation = false;
                if (rule.strict) {
                    // Strictly after (not equal)
                    isViolation = fieldDate <= compareDate;
                } else {
                    // Greater than or equal
                    isViolation = fieldDate < compareDate;
                }

                if (isViolation) {
                    var afterLabel = rule.after_label;
                    if (rule.offset_months) {
                        errors.push(rule.label + ' should be at least ' + rule.offset_months + ' months after ' + afterLabel.replace(' + ' + rule.offset_months + ' months', ''));
                    } else if (rule.strict) {
                        errors.push(rule.label + ' should be after ' + afterLabel);
                    } else {
                        errors.push(rule.label + ' should be greater than or equal to ' + afterLabel);
                    }
                }
            } else if (rule.must_be_after === fieldKey) {
                // We are editing the EARLIER date
                var otherDate = null;
                if (_fields[rule.field]) {
                    otherDate = getEffectiveDate($(_fields[rule.field].input).first());
                }
                if (!otherDate && _context && _context.dates && _context.dates[rule.field]) {
                    otherDate = parseDate(_context.dates[rule.field]);
                }
                
                if (!otherDate) continue;

                var compareDate = fieldDate;
                if (rule.offset_months) {
                    compareDate = addMonths(fieldDate, rule.offset_months);
                }

                var isViolation = false;
                if (rule.strict) {
                    // otherDate MUST be strictly after compareDate
                    isViolation = otherDate <= compareDate;
                } else {
                    isViolation = otherDate < compareDate;
                }

                if (isViolation) {
                    var cleanLabel = rule.after_label.replace(' + ' + rule.offset_months + ' months', '');
                    if (rule.offset_months) {
                        errors.push(cleanLabel + ' should be at least ' + rule.offset_months + ' months before ' + rule.label);
                    } else if (rule.strict) {
                        errors.push(cleanLabel + ' should be before ' + rule.label);
                    } else {
                        errors.push(cleanLabel + ' should be less than or equal to ' + rule.label);
                    }
                }
            }
        }

        return { valid: errors.length === 0, errors: errors };
    }

    /**
     * Show or hide error for a field.
     */
    function updateFieldUI(fieldKey) {
        if (!_fields[fieldKey]) return;

        var $inputs = $(_fields[fieldKey].input);
        
        $inputs.each(function() {
            var $input = $(this);
            
            // Collect all errors for this specific input across ALL field keys that match its selector
            var allErrors = [];
            for (var key in _fields) {
                if (_fields.hasOwnProperty(key)) {
                    // Check if this input matches the selector for 'key'
                    if ($input.is(_fields[key].input)) {
                        var res = validateField(key, $input);
                        if (!res.valid) {
                            allErrors = allErrors.concat(res.errors);
                        }
                    }
                }
            }
            
            // Try to find an error div relative to this input first (for repeated sections)
            var $errorDiv = $input.closest('.row').find(_fields[fieldKey].errorDiv);
            if ($errorDiv.length === 0) {
                // Try parent row
                $errorDiv = $input.closest('.form-group').parent().find(_fields[fieldKey].errorDiv);
            }
            if ($errorDiv.length === 0) {
                // Fallback to global selector
                $errorDiv = $(_fields[fieldKey].errorDiv);
            }
            
            var $errorMsg = $errorDiv.find('span'); // usually inside the div
            if ($errorMsg.length === 0) $errorMsg = $errorDiv;
            
            if (allErrors.length === 0) {
                $errorDiv.hide();
                $input.removeClass('inputHighlight');
                $errorMsg.text('');
            } else {
                $errorMsg.text(allErrors.join(' & '));
                $errorDiv.show();
                $input.addClass('inputHighlight');
            }
        });
    }

    /**
     * Validate all fields and return true if there are errors.
     */
    function validateAll() {
        var hasErrors = false;
        for (var fieldKey in _fields) {
            if (!_fields.hasOwnProperty(fieldKey)) continue;
            
            updateFieldUI(fieldKey);
            
            // Check if any input for this field is invalid
            var $inputs = $(_fields[fieldKey].input);
            $inputs.each(function() {
                var result = validateField(fieldKey, this);
                if (!result.valid) {
                    hasErrors = true;
                }
            });
        }
        return hasErrors;
    }

    /**
     * Attach change/blur listeners to all field inputs.
     */
    function attachListeners() {
        // Use event delegation on the document for dynamically added inputs
        for (var fieldKey in _fields) {
            if (!_fields.hasOwnProperty(fieldKey)) continue;
            
            (function (key) {
                var selector = _fields[key].input;
                $(document).off('change.chronoValidation blur.chronoValidation', selector);
                $(document).on('change.chronoValidation blur.chronoValidation', selector, function () {
                    // Re-validate all inputs for this field to ensure UI is updated properly
                    updateFieldUI(key);
                    // Also re-validate any field that has this key as must_be_after
                    if (_context && _context.rules) {
                        for (var i = 0; i < _context.rules.length; i++) {
                            if (_context.rules[i].must_be_after === key && _fields[_context.rules[i].field]) {
                                updateFieldUI(_context.rules[i].field);
                            }
                        }
                    }
                });
            })(fieldKey);
        }
    }

    /**
     * Attach submit prevention to the form.
     */
    function attachSubmitBlock() {
        if (_submitBtn) {
            var $btn = $(_submitBtn);
            // Remove previous handler if re-initializing
            if (_onSubmitHandler) {
                $btn.off('click.chronoValidation');
            }
            _onSubmitHandler = function (e) {
                if (validateAll()) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    return false;
                }
            };
            $btn.on('click.chronoValidation', _onSubmitHandler);
        }
    }

    /**
     * Initialize the validator.
     */
    function init(options) {
        _fields = options.fields || {};
        _submitBtn = options.submitBtn || null;
        _formSelector = options.formSelector || null;

        var customerId = options.customerId;
        var typedServiceId = options.typedServiceId;
        var serviceType = options.serviceType || '';

        if (!customerId || !typedServiceId) {
            console.warn('DateChronologyValidator: Missing customerId or typedServiceId');
            return;
        }

        // Fetch context from server
        $.ajax({
            url: 'dateChronologyCheck.php',
            type: 'GET',
            dataType: 'json',
            data: {
                customer_id: customerId,
                typed_service_id: typedServiceId,
                service_type: serviceType
            },
            success: function (data) {
                if (data && !data.error) {
                    _context = data;
                    attachListeners();
                    attachSubmitBlock();
                    // Initial validation of any pre-filled fields
                    setTimeout(function () {
                        for (var key in _fields) {
                            if (_fields.hasOwnProperty(key)) {
                                var $input = $(_fields[key].input);
                                if ($input.length && $input.val() && $input.val().trim() !== '') {
                                    updateFieldUI(key);
                                }
                            }
                        }
                    }, 300);
                } else {
                    console.warn('DateChronologyValidator: Server returned error', data);
                }
            },
            error: function (xhr, status, err) {
                console.warn('DateChronologyValidator: AJAX error', status, err);
            }
        });
    }

    /**
     * Get the current context (for debugging or external use).
     */
    function getContext() {
        return _context;
    }

    /**
     * Clear all errors and detach listeners.
     */
    function destroy() {
        for (var fieldKey in _fields) {
            if (!_fields.hasOwnProperty(fieldKey)) continue;
            var $input = $(_fields[fieldKey].input);
            $input.off('change.chronoValidation blur.chronoValidation');
            var $errorDiv = $(_fields[fieldKey].errorDiv);
            if ($errorDiv.length) $errorDiv.hide();
            $input.removeClass('inputHighlight');
        }
        if (_submitBtn && _onSubmitHandler) {
            $(_submitBtn).off('click.chronoValidation');
        }
        _context = null;
        _fields = {};
        _onSubmitHandler = null;
    }

    /**
     * Manually validate a single field and return the result.
     */
    function check(fieldKey) {
        return validateField(fieldKey);
    }

    /**
     * Update the context dates with new values (e.g., when a field changes
     * and we want to update the reference dates for other fields).
     */
    function updateContextDate(fieldKey, isoDateStr) {
        if (_context && _context.dates) {
            _context.dates[fieldKey] = isoDateStr;
        }
    }

    return {
        init: init,
        check: check,
        validateAll: validateAll,
        getContext: getContext,
        destroy: destroy,
        updateContextDate: updateContextDate,
        parseDate: parseDate,
        formatDate: formatDate
    };

})();
