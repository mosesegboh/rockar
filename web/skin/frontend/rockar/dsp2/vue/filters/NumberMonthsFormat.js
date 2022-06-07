import numeral from 'numeral';

export default {
    read(number) {
        number = Math.floor(number);
        return numeral(number).format('0,0') + this.translateString(' Months');
    },
    write(number) {
        return Math.floor(numeral(number).value());
    }
}
