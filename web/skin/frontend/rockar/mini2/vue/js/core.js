// Components
import appPageHeader from 'mini2/components/PageHeader';
import appSaveCar from 'mini2/components/SaveCar';

// CarFinder
import appCarFinder from 'mini2/components/CarFinder/CarFinder';
import appCarFilter from 'mini2/components/CarFinder/CarFilter';
import appCarouselModelMatrix from 'mini2/components/CarFinder/CarouselModelMatrix';
import appFinanceFilter from 'mini2/components/CarFinder/FinanceFilter';
import appModelFilter from 'mini2/components/CarFinder/ModelFilter';
import appProductGrid from 'mini2/components/CarFinder/ProductGrid';
import appProductPod from 'mini2/components/CarFinder/ProductPod';
import appCarCompareModalContent from 'mini2/components/CarFinder/CarCompareModalContent';

// PartExchange
import appPartExchangeBlock from 'mini2/components/PartExchange/PartExchangeBlock/PartExchangeBlock';
import appPartExchangeCarFinder from 'mini2/components/PartExchange/PartExchangeCarFinder';
import appPartExchangeMyAccount from 'mini2/components/PartExchange/PartExchangeMyAccount';
import appPartExchangePdp from 'mini2/components/PartExchange/PartExchangePdp';

// Configurator
import appChooseVehicle from 'mini2/components/Configurator/ChooseVehicle';
import appConfigurator from 'mini2/components/Configurator/Configurator';
import appConfiguratorFinanceQuote from 'mini2/components/Configurator/FinanceQuote';
import appProductTopContainer from 'mini2/components/Configurator/ProductTopContainer';

// Elements
import appGetQuoteCta from 'mini2/components/Elements/GetQuoteCta';

// My Account
import appMySavedCars from 'mini2/components/MyAccount/MySavedCars';
import appDocumentUpload from 'mini2/components/MyAccount/DocumentUpload';
import appMyOrders from 'mini2/components/MyAccount/MyOrders';
import appMyTestDrives from 'mini2/components/MyAccount/MyTestDrives';

// CarCompare
import appCarCompare from 'mini2/components/CarCompare/CarCompare';
import appCarCompareSaveCar from 'mini2/components/CarCompare/CarCompareSaveCar';
import appCarCompareClear from 'mini2/components/CarCompare/CarCompareClear';

// Checkout
import appAccordionNav from 'mini2/components/Checkout/AccordionNav.vue';
import appCheckoutDelivery from 'mini2/components/Checkout/Delivery.vue';
import appCheckoutYourDetails from 'mini2/components/Checkout/YourDetails/YourDetails.vue';
import CheckoutIncomeExpenses from 'mini2/components/Checkout/YourDetails/IncomeExpenses.vue';
import CheckoutCommunicationPreferences from 'mini2/components/Checkout/YourDetails/CommunicationPreferences.vue';
import appCheckoutSummary from 'mini2/components/Checkout/Summary';
import appCheckoutOrderConfirmation from 'mini2/components/Checkout/OrderConfirmation.vue';
import appCheckoutFinanceOverlay from 'mini2/components/Checkout/FinanceOverlay';

let CoreComponents = {
    appAccordionNav,
    appCarCompare,
    appCarCompareSaveCar,
    appCarFinder,
    appCarouselModelMatrix,
    appCheckoutDelivery,
    appCheckoutOrderConfirmation,
    appCheckoutSummary,
    appChooseVehicle,
    appConfigurator,
    appConfiguratorFinanceQuote,
    appFinanceFilter,
    appGetQuoteCta,
    appModelFilter,
    appMyOrders,
    appMySavedCars,
    appMyTestDrives,
    appPageHeader,
    appPartExchangeBlock,
    appPartExchangeCarFinder,
    appPartExchangeMyAccount,
    appPartExchangePdp,
    appDocumentUpload,
    appProductGrid,
    appProductPod,
    appProductTopContainer,
    appSaveCar,
    appCheckoutYourDetails,
    CheckoutIncomeExpenses,
    CheckoutCommunicationPreferences,
    appCarCompareClear,
    appCarCompareModalContent,
    appCheckoutFinanceOverlay,
    appCarFilter
};

const ModuleComponents = require('./modules');
const Dsp2CoreComponents = require('../../../dsp2/vue/js/core');

CoreComponents = Object.assign(Dsp2CoreComponents.default, CoreComponents);

Object.keys(ModuleComponents.exportArr).forEach((module) => {
    CoreComponents = Object.assign(ModuleComponents.exportArr[module], CoreComponents);
});

export default CoreComponents;
