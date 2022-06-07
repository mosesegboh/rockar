import numeral from 'numeral';

export default {
    read(value, format = '0,0', currency = true, round = false) {
        // Workaround https://github.com/adamwdraper/Numeral-js/issues/135
        if (numeral.localeData().delimiters.thousands !== ' ') {
            numeral.localeData().delimiters.thousands = ' ';
        }
        if (value) {
            value = round ? Math.round(value) : value;
            return currency ? numeral(value).format(`$ ${format}`) : numeral(value).format(format);
        } else {
            return currency ? `${currencySymbol} ${0}` : 0;
        }
    },

    write(value) {
        var number = numeral(value).value();
        return isNaN(number) ? 0 : number;
    }
};
