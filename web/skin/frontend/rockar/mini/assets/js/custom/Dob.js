/**
 *
 * Alternative solution for prototype
 *
 * @author Krisjanis Smits <techteam@rockar.com>
* @copyright Copyright (c) 2016 Rockar Ltd (http://rockar.com)
 */

class Dob {
    constructor(container, required, format, minAge = false) {
        container = document.querySelector(container);

        if (container) {
            this.day = container.querySelector('#day');
            this.month = container.querySelector('#month');
            this.year = container.querySelector('#year');
            this.full = container.querySelector('#dob');
            this.advice = container.querySelector('.custom-validation-advice');
        } else {
            return;
        }

        this.required = required;
        this.format = format;
        this.minAge = minAge || false;

        this.initValidator(container);
    }

    initValidator(container) {
        let valueError = false;
        const date = new Date;
        const selectedDate = new Date;
        const currentDate = new Date;

        if (jQuery.validator.methods.validateDOB) {
            return;
        }

        jQuery.validator.addMethod('validateDOB', () => {
            let countDaysInMonth;
            let error = false;
            const day = parseInt(this.day.value, 10) || 0;
            const month = parseInt(this.month.value, 10) || 0;
            const year = parseInt(this.year.value, 10) || 0;

            if (this.day.value.length === 0
                && this.month.value.length === 0
                && this.year.value.length === 0
            ) {
                if (this.required) {
                    error = 'This date is a required value.';
                } else {
                    this.full.value = '';
                }
            } else if (!day || !month || !year) {
                error = 'Please enter a valid full date.';
            } else {
                date.setYear(year);
                date.setMonth(month - 1);
                date.setDate(32);

                selectedDate.setYear(year);
                selectedDate.setMonth(month - 1);
                selectedDate.setDate(day);

                countDaysInMonth = 32 - date.getDate();

                if (!countDaysInMonth || countDaysInMonth > 31) {
                    countDaysInMonth = 31;
                }

                if (day < 1 || day > countDaysInMonth) {
                    error = 'Please enter a valid day (1-%d).';
                } else if (month < 1 || month > 12) {
                    error = 'Please enter a valid month (1-12).';
                } else if (selectedDate > currentDate) {
                    error = 'Please enter valid date from the past.';
                } else {
                    if (day % 10 === day) {
                        this.day.value = `0${day}`;
                    }
                    if (month % 10 === month) {
                        this.month.value = `0${month}`;
                    }

                    this.full.value = this.format
                        .replace(/%[mb]/i, this.month.value)
                        .replace(/%[de]/i, this.day.value)
                        .replace(/%y/i, this.year.value);

                    const testFull = `${this.month.value}/${this.day.value}/${this.year.value}`;
                    const test = new Date(testFull);

                    if (isNaN(test)) {
                        error = 'Please enter a valid date.';
                    } else {
                        if (this.minAge) {
                            if (this.calculateAge(testFull) < this.minAge) {
                                error = `Must be ${this.minAge} years or over.`;
                            } else {
                                this.setFullDate(test);
                            }
                        } else {
                            this.setFullDate(test);
                        }
                    }
                }

                if (!error && !this.validateData(container)) {
                    valueError = this.validateDataErrorText;
                    error = valueError;
                }
            }

            if (error !== false) {
                try {
                    error = Translator.translate(error);
                } catch (e) {
                    console.error(e);
                }

                if (!valueError) {
                    this.advice.innerHTML = error.replace('%d', countDaysInMonth);
                } else {
                    this.advice.innerHTML = this.errorTextModifier(error);
                }

                this.advice.style.display = 'block';
                return false;
            }

            // fixing elements class
            this.day.className.replace(/\bvalidation-failed\b/, '');
            this.month.className.replace(/\bvalidation-failed\b/, '');
            this.year.className.replace(/\bvalidation-failed\b/, '');

            this.advice.style.display = 'none';
            return true;
        }, '');
    }

    validateData(container) {
        jQuery(container).find('.validation-error').each((e) => {
            jQuery(e).removeClass('validation-error');
        });

        const year = this.fullDate.getFullYear();
        const date = new Date;
        this.curyear = date.getFullYear();
        return (year >= 1900 && year <= this.curyear);
    }

    setFullDate(date) {
        this.fullDate = date;
    }

    calculateAge(dateString) {
        const today = new Date();
        const birthDate = new Date(dateString);
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    }
}
