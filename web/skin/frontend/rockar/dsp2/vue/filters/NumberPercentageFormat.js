import numeral from 'numeral';

export default {
    read(number) {
        return `${numeral(Math.floor(number)).format('0')} ${this.translateString('%')}`;
    },

    write(number) {
        return Math.floor(numeral(number).value());
    }
};
