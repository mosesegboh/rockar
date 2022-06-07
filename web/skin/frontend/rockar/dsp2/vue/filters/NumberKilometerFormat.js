import numeral from 'numeral';
import translateString from 'core/filters/Translate';

export default {
    read(number) {
        return `${numeral(Math.floor(number)).format('0,0')} ${translateString('km')}`;
    },
    write(number) {
        return Math.floor(numeral(number).value());
    }
}
