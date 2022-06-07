import numeral from 'numeral';
import translateString from 'core/filters/Translate';

export default {
    read(number) {
        number = Math.abs(Math.floor(number));

        if (isNaN(number)) {
            number = 0;
        }

        if (this.hasMileageFocus) {
            return number !== 0 ? numeral(number).format('0,0').replace(/,/g, ' ') : '';
        } else {
            return `${numeral(number).format('0,0').replace(/,/g, ' ')} ${translateString('km')}`;
        }
    },

    write(number) {
        return Math.abs(Math.floor(numeral(number).value()));
    }
}
