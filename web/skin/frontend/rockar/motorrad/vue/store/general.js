export default {
    state: {
        pxFutureValueBlock: '',
        pxFutureValueEnabled: false,
        pxShowNotification: false,
        pxShowNotificationVehicle: false,
        pxFcConfiguration: '',
        pxMmCode: null,
        pxVrmInput: '',
        pxRegistrationYear: null,
        pxMileage: 0,
        pxCondition: false,
        pxAdditionalInfo: '',
        pxSettlement: 0,
        currentCategory: '',
        brand: '',
        product: {
            'title': '',
            'subtitle': '',
            'price': '',
            'sku': '',
            'configurableSku': '',
            'category': ''
        },
        datepickerReady: false,
        outstandingFinanceSettlement: 0,
        businessPicker: 'individual',
        mySavedCar: {
            pxValue: 0,
            mileage: 0,
            outstandingFinance: 0,
            expireDate: ''
        },
        deepLinkRequestParams: {}
    },

    mutations: {
        setPxFutureValueBlock(state, value) {
            state.pxFutureValueBlock = value;
        },

        setPxFutureValueEnabled(state, value) {
            state.pxFutureValueEnabled = value;
        },

        setPxShowNotification(state, value) {
            state.pxShowNotification = value;
        },

        setPxShowNotificationVehicle(state, value) {
            state.pxShowNotificationVehicle = value;
        },

        setPxFcConfiguration(state, value) {
            state.pxFcConfiguration = value;
        },

        setCurrentCategory(state, value) {
            state.currentCategory = value;
        },

        setBrand(state, value) {
            state.brand = value;
        },

        setProductTitle(state, value) {
            state.product.title = value;
        },

        setProductSubtitle(state, value) {
            state.product.subtitle = value;
        },

        setProductPrice(state, value) {
            state.product.price = value;
        },

        setProductId(state, value) {
            state.product.id = value;
        },

        setProductSku(state, value) {
            state.product.sku = value;
        },

        setConfigurableProductSku(state, value) {
            state.product.configurableSku = value;
        },

        setProductCategory(state, value) {
            state.product.category = value;
        },

        setDatepickerReady(state, value) {
            state.datepickerReady = value;
        },

        setPXOutstandingFinanceSettlement(state, value) {
            state.PX.Valuation.outstandingFinanceSettlement = value;
        },

        setPXVrmCarInfoRegistrationYear(state, value) {
            state.PX.Vrm.carInfo.registrationYear = value;
        },

        setPXRegistrationYear(state, value) {
            state.PX.registrationYear = value;
        },

        setBalloonPercentage(state, value) {
            state.balloonPercentage = value;
        },

        setBusinessPicker(state, value) {
            state.businessPicker = value;
        },

        setMySavedCarPxValue(state, value) {
            state.mySavedCar.pxValue = value;
        },

        setMySavedCarMileage(state, value) {
            state.mySavedCar.mileage = value;
        },

        setMySavedCarOutstandingFinance(state, value) {
            state.mySavedCar.outstandingFinance = value;
        },

        setMySavedCarExpireDate(state, value) {
            state.mySavedCar.expireDate = value;
        },

        setDeepLinkRequestParams(state, value) {
            state.deepLinkRequestParams = value;
        }
    }
}

