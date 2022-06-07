export default {
    props: {
        globalCssClasses: {
            type: Object,
            required: false,
            default: () => ({
                requiredLabel: 'side-label required'
            })
        },
        validationMessages: {
            type: Object,
            required: false,
            default: () => ({
                requiredField: 'This field is required.',
                phoneInvalidFormat: 'Please enter a valid phone number.',
                acceptTCS: 'Please accept terms and conditions.',
                idNumberFormat: 'Please enter a valid South African identity number.',
                emailInvalidFormat: 'Please enter a valid email address.',
                postcodeNumberFormat: 'Please enter a valid South African postcode number.'
            })
        }
    },

    computed: {
        /**
         *Returns css classes for a required field.
         */
        cssClassForRequiredLabel() {
            return this.globalCssClasses.requiredLabel;
        },

        /**
         * returns the error message when a required field is not completed.
         */
        requiredFieldErrorMessage() {
            return this.validationMessages.requiredField;
        },

        /**
        message to display when a phone number is in the invalid format.
         */
        phoneInvalidFormatErrorMessage() {
            return this.validationMessages.phoneInvalidFormat;
        },

        /**
         Message to display when user has not accepted terms and conditions.
         */
        acceptTCSValidationMessage() {
            return this.validationMessages.acceptTCS;
        },

        invalidIdNumberFormatValidationMessage() {
            return this.validationMessages.idNumberFormat;
        },

        invalidEmailFormatValidationMessage() {
            return this.validationMessages.emailInvalidFormat;
        },

        /**
         Message to display when a postal code is in the invalid format.
         */
        invalidPostcodeFormatValidationMessage() {
            return this.validationMessages.postcodeNumberFormat;
        }
    }
};
