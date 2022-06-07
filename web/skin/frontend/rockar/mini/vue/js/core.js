// General
import appSearch from 'core/components/Elements/Search';

// Car Compare
import appCarCompare from 'mini/components/CarCompare/CarCompare.vue';
import carCompareSaveCar from 'mini/components/CarCompare/CarCompareSaveCar.vue';

// Car Finder
import appCarFinder from 'mini/components/CarFinder/CarFinder';
import appCarFilter from 'mini/components/CarFinder/CarFilter';
import appModelFilter from 'mini/components/CarFinder/ModelFilter';
import appFinanceFilter from 'mini/components/CarFinder/FinanceFilter';
import appProductGrid from 'mini/components/CarFinder/ProductGrid';
import appProductPod from 'mini/components/CarFinder/ProductPod';
import appCarCompareLink from 'mini/components/CarFinder/CarCompareLink';
import appCarFinderPartExchange from 'mini/components/CarFinder/PartExchange';
import appCarFinderSelect from 'core/components/CarFinder/Select';

// Configurator
import appConfigurator from 'mini/components/Configurator/Configurator';
import appAccessories from 'core/components/Configurator/Accessories';
import appChooseVehicle from 'core/components/Configurator/ChooseVehicle';
import appChooseVehicleGrid from 'core/components/Configurator/ChooseVehicleGrid';
import appConfiguratorFinanceQuote from 'mini/components/Configurator/FinanceQuote';
import appConfiguratorCarouselImages from 'core/components/Configurator/CarouselImages';
import appTopFinanceQuote from 'core/components/Configurator/TopFinanceQuote';
import appProductTopContainer from 'mini/components/Configurator/ProductTopContainer';
import appExperiencePopup from 'mini/components/Configurator/ExperiencePopup';

// Full Configurator
import appFullConfigurator from 'core/components/FullConfigurator/FullConfigurator.vue';
import appSummary from 'core/components/FullConfigurator/Summary.vue';
import appYouBuildCategory from 'core/components/YouBuildCategory/YouBuildCategory.vue';
import appYouBuildProductGrid from 'core/components/YouBuildCategory/ProductGrid.vue';
import appYouBuildProductPod from 'core/components/YouBuildCategory/ProductPod.vue';
import appYouBuildCarFilter from 'core/components/YouBuildCategory/YouBuildCarFilter.vue';

// Gcdm
import appGcdmPopup from 'mini/components/Gcdm/Popup.vue';

// My Account
import appAccountPartExchange from 'mini/components/MyAccount/PartExchange';
import appAccountPartExchangeValuation from 'mini/components/MyAccount/PartExchangeValuation';
import appMyDetails from 'mini/components/MyAccount/MyDetails';
import appMyCurrentCars from 'mini/components/MyAccount/MyCurrentCars';
import appMyCurrentCarsPx from 'core/components/MyAccount/MyCurrentCarsPX';
import appMyOrders from 'mini/components/MyAccount/MyOrders';
import appMySavedCars from 'mini/components/MyAccount/MySavedCars';
import appMyServices from 'core/components/MyAccount/MyServices.vue';
import appMyTestDrives from 'mini/components/MyAccount/MyTestDrives.vue';
import appDocumentUpload from 'bmw/components/MyAccount/DocumentUpload.vue';

// Checkout
import appCheckout from 'mini/components/Checkout/Checkout';
import appCheckoutAccordion from 'mini/components/Checkout/Accordion';
import appCheckoutAccordionGroup from 'mini/components/Checkout/AccordionGroup';
import appCheckoutSummary from 'mini/components/Checkout/Summary';
import appCheckoutPartExchange from 'mini/components/Checkout/PartExchange';
import appCheckoutDelivery from 'mini/components/Checkout/Delivery';
import appCheckoutFinanceOverlay from 'mini/components/Checkout/FinanceOverlay';
import appCheckoutFinanceQuote from 'mini/components/Checkout/FinanceQuote';
import appCheckoutOrderConfirmation from 'mini/components/Checkout/OrderConfirmation';
import appCheckoutYourDetails from 'mini/components/Checkout/YourDetails/YourDetails';
import appCheckoutYourPaymentOption from 'core/components/Checkout/YourPaymentOption';
import appCheckoutAddress from 'mini/components/Checkout/Address';
import appOtpPopup from 'mini/components/Checkout/YourDetails/OtpPopup';

// Trade in
import appPartExchange from 'mini/components/PartExchange/PartExchange';

// Elements
import appDatepicker from 'core/components/Elements/DatePicker';
import appGetQuoteCta from 'mini/components/Elements/GetQuoteCta';
import appMaps from 'mini/components/Elements/Maps';
import appCalendar from 'core/components/Elements/Calendar';
import appModal from 'core/components/Elements/Modal';
import appRangeSlider from 'mini/components/Elements/RangeSlider';
import appSelect from 'core/components/Elements/Select';
import appTimepicker from 'mini/components/Elements/TimePicker';
import appTooltip from 'core/components/Elements/Tooltip';
import appConfirmationModal from 'core/components/Elements/ConfirmationModal';
import appScrollbox from 'core/components/Elements/Scrollbox';
import appHomepageSlider from 'core/components/Elements/Sliders/Homepage';
import appMoreInfo from 'core/components/Elements/MoreInfo';
import appAttributes from 'mini/components/Elements/Attributes';
import appOfferTags from 'mini/components/Elements/OfferTags';

// YouDrive
import appYouDrive from 'mini/components/YouDrive/YouDrive';

// Approved Used
import appApprovedUsedCategory from 'core/components/ApprovedUsedCategory/ApprovedUsedCategory';
import appApprovedUsedProductGrid from 'core/components/ApprovedUsedCategory/ProductGrid';
import appApprovedUsedProductPod from 'core/components/ApprovedUsedCategory/ProductPod';
import appApprovedUsedCarFilter from 'core/components/ApprovedUsedCategory/CarFilter';
import appApprovedUsedModelFilter from 'core/components/ApprovedUsedCategory/ModelFilter';
import appApprovedUsedConfigurator from 'core/components/ApprovedUsedConfigurator/ApprovedUsedConfigurator';
import appApprovedUsedConfiguratorFinanceQuote from 'core/components/ApprovedUsedConfigurator/ApprovedUsedConfiguratorFinanceQuote';
import appApprovedUsedProductTopContainer from 'core/components/ApprovedUsedConfigurator/ApprovedUsedProductTopContainer';

// Policy
import appPolicyPopup from 'mini/components/Gcdm/PolicyPopup.vue';
import appAccountMissingDetailsPopup from 'mini/components/Elements/AccountMissingDetailsPopup';

// General
import appAccordion from 'core/components/Accordion';
import appAccordionGroup from 'core/components/AccordionGroup';
import appAccordionTabs from 'core/components/AccordionTabs';
import appCarouselColor from 'core/components/CarouselColor';
import atTab from 'core/components/AccordionTabsContent';
import appCarousel from 'core/components/Carousel';
import appCarouselGeneral from 'core/components/CarouselGeneral';
import appCarouselModel from 'core/components/CarouselModel';
import appFinanceOverlay from 'mini/components/FinanceOverlay';
import appFinanceQuote from 'core/components/FinanceQuote';
import appSaveCar from 'mini/components/SaveCar';
import appNotification from 'core/components/Notification';
import appRegister from 'core/components/Register';
import appInstoreSessionTimeout from 'core/components/InstoreSessionTimeout';

var CoreComponents = {
    appAccessories,
    appAccordion,
    appAccordionGroup,
    appAccordionTabs,
    appAccountPartExchange,
    appAccountPartExchangeValuation,
    appApprovedUsedCategory,
    appApprovedUsedProductGrid,
    appApprovedUsedProductPod,
    appApprovedUsedCarFilter,
    appApprovedUsedModelFilter,
    appApprovedUsedConfigurator,
    appApprovedUsedConfiguratorFinanceQuote,
    appApprovedUsedProductTopContainer,
    atTab,
    appCalendar,
    appCarCompare,
    carCompareSaveCar,
    appCarCompareLink,
    appCarFilter,
    appModelFilter,
    appCarFinder,
    appCarFinderPartExchange,
    appCarousel,
    appCarouselColor,
    appCarouselGeneral,
    appCarouselModel,
    appCheckout,
    appCheckoutAccordion,
    appCheckoutAccordionGroup,
    appCheckoutSummary,
    appCheckoutPartExchange,
    appCheckoutDelivery,
    appCheckoutFinanceOverlay,
    appCheckoutFinanceQuote,
    appCheckoutYourDetails,
    appCheckoutYourPaymentOption,
    appCheckoutOrderConfirmation,
    appCheckoutAddress,
    appConfiguratorFinanceQuote,
    appConfirmationModal,
    appFinanceOverlay,
    appChooseVehicle,
    appChooseVehicleGrid,
    appConfigurator,
    appConfiguratorCarouselImages,
    appDatepicker,
    appDocumentUpload,
    appGetQuoteCta,
    appFinanceFilter,
    appFinanceQuote,
    appFullConfigurator,
    appGcdmPopup,
    appAccountMissingDetailsPopup,
    appMaps,
    appModal,
    appMyDetails,
    appMyCurrentCars,
    appMyCurrentCarsPx,
    appMyOrders,
    appMySavedCars,
    appSaveCar,
    appMyTestDrives,
    appMyServices,
    appNotification,
    appPartExchange,
    appPolicyPopup,
    appProductGrid,
    appProductPod,
    appProductTopContainer,
    appRangeSlider,
    appSelect,
    appSearch,
    appSummary,
    appTimepicker,
    appTooltip,
    appTopFinanceQuote,
    appYouDrive,
    appRegister,
    appScrollbox,
    appHomepageSlider,
    appMoreInfo,
    appYouBuildCategory,
    appYouBuildProductGrid,
    appYouBuildProductPod,
    appYouBuildCarFilter,
    appInstoreSessionTimeout,
    appCarFinderSelect,
    appOtpPopup,
    appAttributes,
    appOfferTags,
    appExperiencePopup
};

var ModuleComponents = require('./modules');

Object.keys(ModuleComponents.exportArr).forEach((module) => {
    CoreComponents = Object.assign(ModuleComponents.exportArr[module], CoreComponents);
});

export default CoreComponents;
