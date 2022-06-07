import numeral from 'numeral';
import { PhoneNumberFormat, PhoneNumberUtil } from 'google-libphonenumber';


export default {
    props: {
        currentLocale: {
            required: false,
            type: String,
            default: 'ZA'
        },
        currentLocaleInternationalFormat: {
            required: false,
            type: String,
            default: 'en-za'
        }
    },

    computed: {
        numeral() {
            if (numeral.locales[this.currentLocaleInternationalFormat] === undefined) {
                const locale = {
                    delimiters: {
                        thousands: ',',
                        decimal: '.'
                    },

                    abbreviations: {
                        thousand: 'k',
                        million: 'm',
                        billion: 'b',
                        trillion: 't'
                    },

                    ordinal(number) {
                        return number;
                    },

                    currency: {
                        symbol: 'R'
                    }
                };
                numeral.register('locale', this.currentLocaleInternationalFormat, locale);
                numeral.locale(this.currentLocaleInternationalFormat);
                numeral.defaultFormat(`$0${locale.delimiters.thousands}0${locale.delimiters.decimal}00`);
            }
            return numeral;
        }
    },

    data() {
        return {
            localeValidation: {
                ZA: {
                    mobileRegEx: /^\+27\s?[6-8][0-9]\s?[0-9]{3}\s?[0-9]{4}$/,
                    landLineRegEx: /^\+27\s?[1-5][0-9]\s?[0-9]{3}\s?[0-9]{4}$/,
                    mobileOrLandLine: /^\+27\s?[0-9]{2}\s?[0-9]{3}\s?[0-9]{4}$/,
                    companyRegistrationNumber: /^(1|2)[0-9]{3}\/[0-9]{6}\/[0-9]{2}$/,
                    vatNumber: /^4[0-9]{9}$/,
                    postcodeNumber: /^[0-9]{4}$/
                }
            }
        }
    },

    methods: {
        /**
         * Creates a local instance of the numeral import.
         * @param val, {String} value to construct a numeral from
         */
        getNumeralFormatting(val) {
            return this.numeral(val);
        },

        /**
         * Formats the input correctly, allows input to be located in one place.
         * @param {Event} eve - the event object, needed to get a reference to the input element.
         */
        mobileInputBlurManipulator(eve) {
            try {
                const phoneUtil = PhoneNumberUtil.getInstance(),
                    number = phoneUtil.parseAndKeepRawInput(eve.currentTarget.value, this.currentLocale);
                if (phoneUtil.format(number, PhoneNumberFormat.INTERNATIONAL) !== eve.currentTarget.value && phoneUtil.isValidNumber(number)) {
                    eve.currentTarget.value = phoneUtil.format(number, PhoneNumberFormat.INTERNATIONAL);
                    eve.currentTarget.dispatchEvent(new Event('input'));// send value back to v-model.
                }
            } catch (ex) {
                console.error(ex);
            }
        },

        /**
         * ok the idea here is when the user enters a valid number eg 0112203030, we fire the mobileBlurEvent
         * so the formatting and validation will try pass.
         * @param {event} - the event object, needed to get reference to the input element.
         */
        fireManipulateMobile(eve) {
            const value = eve.currentTarget.value;
            if (value.length >= 7) {
                this.mobileInputBlurManipulator(eve);
            }
        }
    },

    filters: {
        currency: {
            read(value) {
                return value === 0 ? '' : this.numeral(value).format();
            },
            write(value) {
                return value === '' ? 0 : this.numeral(value).value();
            }
        }
    }
}
