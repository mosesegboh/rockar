import defaultFilters from 'core/filters';
import xssFilter from './XssFilter';

defaultFilters.push(
    {
        name: 'filterXss',
        content: xssFilter
    }
);

export default defaultFilters;
