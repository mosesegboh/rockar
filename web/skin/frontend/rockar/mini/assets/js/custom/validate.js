/* eslint-disable */

/**
 * Extend jQuery validator plugin to support clear button which wipes all error messages
 *
 * @author Krisjanis Smits <krisjanis@scandwieb.com>
* @copyright Copyright (c) 2016 Rockar Ltd (http://rockar.com)
 */

jQuery.extend(jQuery.validator.defaults, { clearerElement: '.form-clearer' });

jQuery.validator.prototype.init = function() {
    this.labelContainer = jQuery(this.settings.errorLabelContainer);
    this.errorContext = this.labelContainer.length && this.labelContainer || jQuery(this.currentForm);
    this.containers = jQuery(this.settings.errorContainer).add(this.settings.errorLabelContainer);
    this.submitted = {};
    this.valueCache = {};
    this.pendingRequest = 0;
    this.pending = {};
    this.invalid = {};
    this.reset();
    this.canSend = true;

    var groups = (this.groups = {}),
        rules;
    jQuery.each(this.settings.groups, function(key, value) {
        if (typeof value === 'string') {
            value = value.split(/\s/);
        }
        jQuery.each(value, function(index, name) {
            groups[name] = key;
        });
    });
    rules = this.settings.rules;
    jQuery.each(rules, function(key, value) {
        rules[key] = jQuery.validator.normalizeRule(value);
    });

    function delegate(event) {
        var validator = jQuery.data(this.form, 'validator'),
            eventType = `on${event.type.replace(/^validate/, '')}`,
            settings = validator.settings;
        if (settings[eventType] && !jQuery(this).is(settings.ignore)) {
            settings[eventType].call(validator, this, event);
        }
    }

    jQuery(this.currentForm)
        .on('focusin.validate focusout.validate keyup.validate',
            ':text, [type="password"], [type="file"], select, textarea, [type="number"], [type="search"], ' +
            '[type="tel"], [type="url"], [type="email"], [type="datetime"], [type="date"], [type="month"], ' +
            '[type="week"], [type="time"], [type="datetime-local"], [type="range"], [type="color"], ' +
            '[type="radio"], [type="checkbox"], [contenteditable]', delegate)

    .on('change.validate',
        'input.validate-change, select.validate-change',
        function(event) {
            this.element(event.target);
        }.bind(this))

    // Support: Chrome, oldIE
    // 'select' is provided as event.target when clicking a option
    .on('click.validate', 'select, option, [type="radio"], [type="checkbox"]', delegate);

    if (this.settings.invalidHandler) {
        jQuery(this.currentForm).on('invalid-form.validate', this.settings.invalidHandler);
    }

    // Add aria-required to any Static/Data/Class required fields before first validation
    // Screen readers require this attribute to be present before the initial submission http://www.w3.org/TR/WCAG-TECHS/ARIA2.html
    jQuery(this.currentForm).find('[required], [data-rule-required], .required').attr('aria-required', 'true');

    // reset form on button
    if (jQuery(this.currentForm).find(this.settings.clearerElement).length) {
        jQuery(this.currentForm).find(this.settings.clearerElement).click(function() {
            // if form invisible, toggle it before validating
            if (jQuery(this.currentForm).is(':hidden')) {
                var hiddenParent = jQuery(this.currentForm).parents(':hidden:last');
                hiddenParent.show();
                this.checkForm();
                hiddenParent.hide();
            } else {
                this.checkForm();
            }
        }.bind(this));
    }
};

jQuery.fn.validate = function(options) {
    // If nothing is selected, return nothing; can't chain anyway
    if (!this.length) {
        if (options && options.debug && window.console) {
            console.warn('Nothing selected, can\'t validate, returning nothing.');
        }

        return;
    }

    // Check if a validator for this form was already created
    var validator = jQuery.data(this[0], 'validator');
    if (validator) {
        return validator;
    }

    // Add novalidate tag if HTML5.
    this.attr('novalidate', 'novalidate');

    validator = new jQuery.validator(options, this[0]);
    jQuery.data(this[0], 'validator', validator);

    if (validator.settings.onsubmit) {
        this.on('click.validate', ':submit', function(event) {
            validator.cancelSubmit = false;
            if (validator.settings.submitHandler) {
                validator.submitButton = event.target;
            }

            // Allow suppressing validation by adding a cancel class to the submit button
            if (jQuery(this).hasClass('cancel')) {
                validator.cancelSubmit = true;
            }

            // Allow suppressing validation by adding the html5 formnovalidate attribute to the submit button
            if (jQuery(this).attr('formnovalidate') !== undefined) {
                validator.cancelSubmit = true;
            }
        });

        // Validate the form on submit
        this.on('submit.validate', function(event) {
            if (validator.settings.debug) {
                // Prevent form submit to be able to see console output
                event.preventDefault();
            }

            function handle() {
                var hidden,
                    result;

                if (validator.settings.submitHandler) {
                    if (validator.submitButton) {
                        /**
                         * If form has submit button, disable it
                         */
                        if (jQuery(validator.currentForm).find(':submit').length) {
                            jQuery(validator.currentForm).find(':submit').prop('disabled', true);
                        }

                        // Insert a hidden input as a replacement for the missing submit button
                        hidden = jQuery('<input type="hidden">')
                            .attr('name', validator.submitButton.name)
                            .val(jQuery(validator.submitButton).val())
                            .appendTo(validator.currentForm);
                    }

                    result = validator.settings.submitHandler.call(validator, validator.currentForm, event);
                    if (validator.submitButton) {
                        // And clean up afterwards; thanks to no-block-scope, hidden can be referenced
                        hidden.remove();
                    }

                    if (result !== undefined) {
                        return result;
                    }

                    return false;
                }

                return true;
            }

            // Prevent submit for invalid forms or custom submit handlers
            if (validator.cancelSubmit) {
                validator.cancelSubmit = false;
                jQuery(validator.currentForm).find(':submit').prop('disabled', true);
                return handle();
            }

            if (validator.form()) {
                if (validator.pendingRequest) {
                    validator.formSubmitted = true;
                    return false;
                }

                return handle();
            } else {
                validator.focusInvalid();
                return false;
            }
        });
    }

    return validator;
};
