import numeral from 'numeral';

export default {
    read(number) {
        number = Math.floor(number);
        return `${currencySymbol} ${numeral(number).format('0,0').replace(/,/g, ' ')}`;
    },

    write(number) {
        return Math.floor(numeral(number).value());
    }
};
