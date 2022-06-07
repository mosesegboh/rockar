// CarFinder
import appModelFilter from 'motorrad2/components/CarFinder/ModelFilter';
import appFinanceFilter from 'motorrad2/components/CarFinder/FinanceFilter';
import appProductGrid from 'motorrad2/components/CarFinder/ProductGrid';
import appResultsFilter from 'motorrad2/components/CarFinder/ResultsFilter';
import appCarFilter from 'motorrad2/components/CarFinder/CarFilter';
import appProductPod from 'motorrad2/components/CarFinder/ProductPod';

// Configurator
import appConfigurator from 'motorrad2/components/Configurator/Configurator';
import appProductTopContainer from 'motorrad2/components/Configurator/ProductTopContainer';
import appConfiguratorFinanceQuote from 'motorrad2/components/Configurator/FinanceQuote';

// Elements
import appPodSummarySpecs from 'motorrad2/components/Elements/PodSummarySpecs';
import appGetQuoteCta from 'motorrad2/components/Elements/GetQuoteCta';

// PartExchange
import appPartExchangeBlock from 'motorrad2/components/PartExchange/PartExchangeBlock/PartExchangeBlock';

let CoreComponents = {
    appModelFilter,
    appFinanceFilter,
    appConfigurator,
    appProductTopContainer,
    appPodSummarySpecs,
    appProductGrid,
    appResultsFilter,
    appCarFilter,
    appProductPod,
    appPartExchangeBlock,
    appGetQuoteCta,
    appConfiguratorFinanceQuote
};

const ModuleComponents = require('./modules');
const Dsp2CoreComponents = require('../../../dsp2/vue/js/core');

CoreComponents = Object.assign(Dsp2CoreComponents.default, CoreComponents);

Object.keys(ModuleComponents.exportArr).forEach((module) => {
    CoreComponents = Object.assign(ModuleComponents.exportArr[module], CoreComponents);
});

export default CoreComponents;
