const OVERLAYSEARCHPARAMS = {
    OVERLAY: 'overlay',
    FINANCE: 'finance',
    TRADEIN: 'tradein'
};

const PXSTEPS = {
    VALUE_CURRENT_CAR: 10,  // Step 1
    CONDITION_INFO: 11,     // Step 2
};

const PXACCESSPOINTS = {
    CARFINDER: 'carfinder',
    PDP: 'pdp',
    CHECKOUT: 'checkout',
    MYACCOUNT: 'myaccount'
}

const SHORTFALL = {
    TRADEIN_SHORTFALL_NONE: 0,             // Not set / None.
    TRADEIN_SHORTFALL_MONTHLY_PAYMENT: 1,  // Pay-off trade-in shortfall on a monthly basis.
    TRADEIN_SHORTFALL_ONE_CASH_PAYMENT: 2, // Settle trade-in shortfall in a single lump sum payment.
};

const THREESIXTYIMAGECOUNT = 36;

const CHECKOUT_DELIVERY = 1;
const CHECKOUT_FINANCE_STEP = 3;
const CHECKOUT_SUMMARY_STEP = 5;

const VISIBLEINFILTERVALUES = {
    NOT_VISIBLE: 0,
    CATALOG_ONLY: 1,
    YOUDRIVE_ONLY: 2,
    CATALOG_AND_YOUDRIVE: 3
}

export default {
    data() {
        return {
            OVERLAYSEARCHPARAMS,
            PXSTEPS,
            SHORTFALL,
            THREESIXTYIMAGECOUNT,
            VISIBLEINFILTERVALUES,
            CHECKOUT_FINANCE_STEP,
            CHECKOUT_SUMMARY_STEP,
            PXACCESSPOINTS,
            CHECKOUT_DELIVERY
        }
    }
};
