import defaultFilters from 'core/filters';
import xssFilter from './XssFilter';
import numberCurrencyFormat from './NumberCurrencyFormat';
import numberFormat from './NumberFormat';
import numberInputFormat from './NumberInputFormat';
import numberPercentageFormat from './NumberPercentageFormat';
import numberMonthsFormat from './NumberMonthsFormat';
import numberKilometerFormat from './NumberKilometerFormat';
import numberKilometerFormatPx from './NumberKilometerFormatPx';

defaultFilters.push(
    {
        name: 'filterXss',
        content: xssFilter
    },
    {
        name: 'numberCurrencyFormat',
        content: numberCurrencyFormat
    },
    {
        name: 'numberFormat',
        content: numberFormat
    },
    {
        name: 'numberInputFormat',
        content: numberInputFormat
    },
    {
        name: 'numberPercentageFormat',
        content: numberPercentageFormat
    },
    {
        name: 'numberMonthsFormat',
        content: numberMonthsFormat
    },
    {
        name: 'numberKilometerFormat',
        content: numberKilometerFormat
    },
    {
        name: 'numberKilometerFormatPx',
        content: numberKilometerFormatPx
    }
);

export default defaultFilters;
