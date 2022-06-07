import validations from 'core/utils/VueValidation';
import moment from 'moment';
import { PhoneNumberUtil } from 'google-libphonenumber';

/**
 * Replaces Zend based date format into moment
 * @param {String} dateFormat - Zend based date format in
 * @return {String} - moment date format out
 */
function transformServerDateFormat(dateFormat) {
    return dateFormat
        .replace(/%[mb]/i, 'MM')
        .replace(/%[de]/i, 'DD')
        .replace(/%y/i, 'YYYY');
}

/**
 * Validates date based on the specific format
 * @param {String} value - Full date value
 * @param {String} dateFormat - Zend based date format
 * @return {Boolean}
 */
function validatePepDate(value, dateFormat) {
    return moment(value, transformServerDateFormat(dateFormat)).isValid();
}

/**
 * Validates date of birth based on the specific format and target age
 * @param {String} value - Full date value
 * @param {Array} [dobConfig=[21, 'YYYY-MM-DD']] - tuple of target and dateFormat
 * @param {Number} dobConfig[0] - the target age to validate the DOB
 * @param {String} dobConfig[1] - Zend based date format
 * @return {Boolean}
 */
function validatePepDOB(value, dobConfig = [21, 'YYYY-MM-DD']) {
    const target = dobConfig[0],
        dateFormat = transformServerDateFormat(dobConfig[1]),
        age = moment().diff(moment(value, dateFormat), 'years');

    return age >= target;
}

/**
 * Generates Luhn algorithm check digit, required for SAID validation
 * @param {string} inputString
 * @return {number} calculated Luhn check digit
 */
function generateLuhnDigit(inputString) {
    let total = 0,
        count = 0;

    for (let i = 0; i < inputString.length; i++) {
        const multiple = (count++ % 2) + 1,
            temp = multiple * Number(inputString[i]);
        total += Math.floor(temp / 10) + (temp % 10);
    }

    return (total * 9) % 10;
}

/**
 * Checks if an SAID idNumber has a correctly generated Luhn check digit
 * @param {string} idNumber
 * @return {boolean} Luhn check digit is valid or not
 */
function checkSaidIdNumber(idNumber) {
    return generateLuhnDigit(idNumber.substring(0, idNumber.length - 1)) === Number(idNumber[idNumber.length - 1]);
}

/**
 * Extracts date of birth from a SAID, required for SAID validation
 * @param {string} idNumber
 * @return {date} date of birth of idNumber
 */
function getSaidIdBirthDate(idNumber) {
    const idShortYear = idNumber.substring(0, 2),
        currentCentury = new Date().getFullYear() % 100,
        idCentury = Number(idShortYear) < currentCentury ? '20' : '19',
        idMonth = idNumber.substring(2, 4),
        idDay = idNumber.substring(4, 6);

    return new Date(`${idCentury}${idShortYear}/${idMonth}/${idDay}`);
}

/**
 * Extracts gender from a SAID, required for SAID validation
 * @param {string} idNumber
 * @return {string/boolean} 'female' or 'male' if valid, 'false' if invalid
 */
function getSaidIdGender(idNumber) {
    const idGender = parseInt(idNumber.substring(6, 7));
    let result = false;

    if (!isNaN(Number(idGender))) {
        result = Number(idGender) < 5 ? 'female' : 'male';
    }

    return result;
}

/**
 * Extracts citizenship from a SAID, required for SAID validation
 * @param {string} idNumber
 * @return {string/boolean} 'citizen' or 'resident' if valid, 'false' if invalid
 */
function getSaidIdCitizenship(idNumber) {
    const citizenshipString = idNumber.substring(10, 11);
    let result = false;

    if (!isNaN(Number(citizenshipString))) {
        result = Number(citizenshipString) === 0 ? 'citizen' : 'resident';
    }

    return result;
}

/**
 * Performs a full validation for a SAID, checks length, Luhn digit, date of birth, gender and citizenship
 * @param {string} value of the SaIdId to be validated,
 * @param {array} saidTypes like [form_data_selected_document_type_id, said_id_type_id_sent_from_bknd]
 * @return {boolean} SAID if valid or not
 */
function validateSaIdIdNumber(value, saidTypes = [11, 22]) {
    let result = false;
    if (saidTypes[0] === saidTypes[1] && saidTypes[0] !== undefined) {
        if (value !== undefined && value.length === 13) {
            result = !isNaN(getSaidIdBirthDate(value).getTime()) &&
                getSaidIdGender(value) &&
                getSaidIdCitizenship(value) &&
                checkSaidIdNumber(value);
        }
    } else {
        result = true;
    }

    return result;
}

/**
 * Performs simple validation on a field for mobile/landline telephone numbers. Please note locale is defined in main.js.
 * two variables $localeValidation and $currentLocale
 * @param {string} value that will be validated.
 * @param {array} - localeValidation[0] country code, localeValidation[1] regex to validate the country code.
 */
function validateMobile(value, localeValidation) {
    try {
        const phoneNumberUtil = PhoneNumberUtil.getInstance(),
            number = phoneNumberUtil.parseAndKeepRawInput(value, localeValidation[0]);
        if (phoneNumberUtil.isPossibleNumber(number)) {
            return phoneNumberUtil.isValidNumber(phoneNumberUtil.parse(value, localeValidation[0])) && localeValidation[1].test(value);
        }
    } catch (e) {
        console.error(e);
    }

    return false;
}

/**
 * Performs simple validation on a field for mobile/landline numbers.
 * @param {string} value that will be validated. Value can be empty
 * @param {array} localeValidation[0] --> country code, localeValidation[1] regex to validate the country code.
 */
function validateMobileOrEmpty(value, localeValidation) {
    return validateMobile(value, localeValidation) || value === '';
}

/**
 * Performs simple validation on a field for eail address.
 * @param {string} value that will be validated. Value can be empty.
 */
function emailOrEmpty(value) {
    return /^.+@.+\..+$/.test(value) || value === '';
}

/**
 * Does the affiliation code exist
 * @param value the value to validate
 * @param target the target control
 * @returns {Boolean} true the affiliation code exists, false it does not.
 */
function affiliateCodeExists(value, target) {
    return (value === '') ? true : Boolean(target.find(group => group.affiliate_code === value));
}

/**
 * does the the value passed to this function match the pattern of
 * a company registration number.
 * @param {String} value, the company registration number to parse.
 * @param {String} regex, the regular expression to verify the company registration number
 */
function applyRegex(value, regex) {
    return regex.test(value);
}

/**
 * performs validation on the given fields, must follow the regular expression
 * or be empty
 * @param {String} value, value to be tested
 * @param {String} regex, regex to test
 * @returns {boolean}, true --> the validation passed, false validation failed
 */
function regexOrEmpty(value, regex) {
    return regex.test(value) || !value || value.length === 0;
}

export default validations.concat([
    {
        name: 'dob',
        content: validatePepDOB
    },
    {
        name: 'date',
        content: validatePepDate
    },
    {
        name: 'said',
        content: validateSaIdIdNumber
    },
    {
        name: 'mobile',
        content: validateMobile
    },
    {
        name: 'mobileorempty',
        content: validateMobileOrEmpty
    },
    {
        name: 'emailorempty',
        content: emailOrEmpty
    },
    {
        name: 'affiliatecodeexists',
        content: affiliateCodeExists
    },
    {
        name: 'regex',
        content: applyRegex
    },
    {
        name: 'regexorempty',
        content: regexOrEmpty
    }
]);
