import appTooltip from 'core/components/Elements/Tooltip';
import appRangeSlider from 'core/components/Elements/RangeSlider';
import appPartExchangeVrm from 'mini/components/PartExchange/PartExchangeVRM';
import appPartExchangeCustomCar from 'mini/components/PartExchange/PartExchangeCustomCar';
import translateString from 'core/filters/Translate';
import NumbericInputMixin from 'core/mixins/NumericInput';

import numeral from 'numeral';

export default {
    mixins: [NumbericInputMixin],

    props: {
        peId: {
            required: false,
            type: Number
        },
        additionalInfo: {
            required: false,
            type: Array,
            default() {
                return [];
            }
        },
        explanatoryText: {
            required: false,
            type: String,
            default: ''
        },
        carDetailsUrl: {
            required: false,
            type: String
        },
        valuationUrl: {
            required: false,
            type: String
        },
        saveValuationUrl: {
            required: false,
            type: String
        },
        saveToSessionUrl: {
            required: false,
            type: String
        },
        carAlternativeDetailsUrl: {
            required: false,
            type: String
        },
        resetUrl: {
            required: false,
            type: String
        },
        softResetUrl: {
            required: false,
            type: String
        },
        makeUrl: {
            required: false,
            type: String
        },
        rangeUrl: {
            required: false,
            type: String
        },
        modelUrl: {
            required: false,
            type: String
        },
        derivativeUrl: {
            required: false,
            type: String
        },
        yearUrl: {
            required: false,
            type: String
        },
        colourUrl: {
            required: false,
            type: String
        },
        customCarUrl: {
            required: false,
            type: String
        },
        activePxUrl: {
            required: false,
            type: String
        },
        carConditions: {
            required: false,
            type: Array,
            default: [
                {
                    id: 0,
                    title: 'Blank'
                }
            ]
        },
        activeCondition: {
            required: false,
            type: Number
        },
        activeFinanceSettlementCondition: {
            required: false,
            type: Number
        },
        saved: {
            required: false,
            type: Boolean,
            default: false
        },
        savedPxList: {
            required: false,
            type: Array,
            default() {
                return [];
            }
        },
        savedPx: {
            required: false,
            default: false
        },
        canOpenCustom: {
            required: false,
            type: Boolean,
            default: true
        },
        canEdit: {
            required: false,
            type: Boolean,
            default: true
        },
        isExpired: {
            required: false,
            type: Boolean,
            default: false
        },
        customerIsLoggedIn: {
            required: false,
            type: Boolean
        },
        customerAccountUrl: {
            required: false,
            type: String
        },
        settlementQuotesUrl: {
            required: false,
            type: String
        },
        settlementTermsUrl: {
            required: false,
            type: String,
            default: ''
        }
    },

    data() {
        return {
            mileage: 0,
            ajaxLoading: false,
            openCustom: false,
            valuationResult: false,
            disableSelects: true,
            showCondition: 0,
            hasMileageFocus: false,
            softResetSuccessQueue: []
        };
    },

    computed: {

        PXValuation() {
            return this.$refs.partExchangeValuationResult;
        },

        PXVrm() {
            return this.$refs.partExchangeVrm;
        },

        activeSliderCondition() {
            var activeCondition;

            if (!this.activeCondition) {
                this.carConditions.forEach((condition, index) => {
                    if (condition.is_default) {
                        activeCondition = condition.id;
                        return index;
                    }
                });
            } else {
                activeCondition = this.activeCondition;
            }

            this.activeCondition = activeCondition;
            return activeCondition;
        },

        outstandingFinanceSettlementOptions() {
            return [
                {
                    id: 1,
                    is_default: true,
                    title: 'Add to monthly payment'
                },
                {
                    id: 2,
                    is_default: false,
                    title: 'Once-off payment'
                }
            ];
        },

        outstandingFinanceSettlement() {
            var activeFinanceSettlementCondition;

            if (!this.activeFinanceSettlementCondition) {
                this.outstandingFinanceSettlementOptions.forEach((condition, index) => {
                    if (condition.is_default) {
                        activeFinanceSettlementCondition = condition.id;
                        return index;
                    }
                });
            } else {
                activeFinanceSettlementCondition = this.activeFinanceSettlementCondition;
            }

            this.activeFinanceSettlementCondition = activeFinanceSettlementCondition;
            return activeFinanceSettlementCondition;
        },

        deepLinkParams() {
            return this.$store.state.general.deepLinkRequestParams;
        }
    },

    watch: {
        'openCustom': {
            handler(newValue) {
                if (newValue) {
                    this.$broadcast('PartExchangeCustomCar::customCarInit');
                }
            }
        },

        'additionalInfo': {
            handler() {
                this.checkData();
            },
            deep: true
        },

        'mileage': {
            handler() {
                this.checkData();
            }
        }
    },

    filters: {
        numberFormat: {
            read(number) {
                return numeral(number).format('0,0');
            },

            write(number) {
                return numeral(number).value();
            }
        },

        numberMilesFormat: {
            read(number) {
                number = Math.floor(number);
                if (isNaN(number)) {
                    number = 0;
                }

                if (this.hasMileageFocus) {
                    return number !== 0 ? numeral(number).format('0,0') : '';
                } else {
                    return numeral(number).format('0,0') + this.translateString(' KM');
                }
            },

            write(number) {
                return Math.floor(numeral(number).value());
            }
        }
    },

    events: {
        'PartExchange::changeCondition'(id) {
            if (this.activeCondition !== id) {
                this.activeCondition = id;
                this.setGetValuationStep();
            }
        },

        'PartExchange::updateRegistrationYear'(data) {
            if (!data.registrationYear) {
                this.registrationYear = this.$store.state.general.PX.registrationYear;
            } else {
                this.registrationYear = data.registrationYear;
            }
        },

        'PartExchange::softResetSuccess'() {
            let callback;

            while (callback = this.softResetSuccessQueue.pop()) {
                callback.call();
            }
        }
    },

    methods: {
        translateString,

        getValuation() {
            return this.PXValuation.getValuation();
        },

        checkData() {
            var isValid = true;

            if (this.mileage <= 0 || this.PXVrm.carInfo.vrm === null) {
                isValid = false;
            }

            this.additionalInfo.forEach((option) => {
                if (option.isRequired && !option.checked) {
                    isValid = false;
                }
            });

            if (!this.activeCondition) isValid = false;

            return isValid;
        },

        loadValuation(valuation) {
            if (this.canEdit) {
                this.loadActiveValuation(valuation);
            } else {
                this.ajaxLoading = true;
                this.$http({
                    url: this.activePxUrl,
                    data: {
                        px_id: this.peId
                    }
                }).then(this.loadValuationSuccess, this.loadValuationFail);
            }
        },

        loadActiveValuation(valuation) {
            this.carConditions.forEach((condition, index) => {
                if (condition.id === parseInt(valuation.car_condition)) {
                    this.activeCondition = condition.id;
                }
            });

            if (valuation.additional_info) {
                valuation.additional_info.forEach((checkbox, index) => {
                    this.additionalInfo[index].checked = checkbox.checked === 'true';
                });
            }

            this.mileage = valuation.mileage;
            this.registrationYear = valuation.plate_year;

            this.valuationResult = true;
            this.disableSelects = false;

            this.PXVrm.carInfo.capId = parseInt(valuation.px_id);
            this.PXVrm.carInfo.model = valuation.cap.model;
            this.PXVrm.carInfo.title = valuation.cap_extended.product_name;
            this.PXVrm.carInfo.vrm = valuation.vrm;
            this.PXVrm.carInfo.vrmInput = valuation.vrm;
            this.PXVrm.carInfo.registrationYear = valuation.plate_year;
            this.PXVrm.carDetails.result = true;
            this.PXVrm.carDetails.vrmDisabled = true;

            this.PXValuation.valuationResult = true;
            this.PXValuation.partExchangeValue = parseFloat(valuation.part_exchange_value);
            this.PXValuation.valuationCompleted = true;
            this.PXValuation.outstandingFinance = parseFloat(valuation.outstanding_finance);
        },

        loadValuationSuccess(valuation, noAjax = false) {
            if (!noAjax) {
                valuation = valuation.data;
            }

            this.ajaxLoading = false;

            this.activeCondition = parseInt(valuation.car_condition);
            this.mileage = valuation.mileage;
            this.registrationYear = valuation.plate_year;

            if (valuation.additional_info) {
                valuation.additional_info.forEach((checkbox, index) => {
                    this.additionalInfo[index].checked = checkbox.checked === 'true';
                });
            }

            this.valuationResult = true;
            this.disableSelects = false;
            this.isExpired = valuation.is_expired;

            this.PXVrm.carInfo.capId = parseInt(valuation.px_id);
            this.PXVrm.carInfo.model = valuation.cap.model;
            this.PXVrm.carInfo.title = valuation.cap_extended.product_name;
            this.PXVrm.carInfo.vrm = valuation.vrm;
            this.PXVrm.carInfo.vrmInput = valuation.vrm;
            this.PXVrm.carInfo.registrationYear = valuation.plate_year;
            this.PXVrm.carDetails.result = true;
            this.PXVrm.carDetails.vrmDisabled = true;

            this.PXValuation.partExchangeValue = parseFloat(valuation.part_exchange_value);
            this.PXValuation.outstandingFinance = parseFloat(valuation.outstanding_finance);
        },

        loadValuationFail(error) {
            this.ajaxLoading = false;
            if (typeof error.data.errorMessage !== 'undefined') {
                console.error(error.data.errorMessage);
            } else {
                console.error(error.statusText);
            }
        },

        resetFilters(closeAccordion = false, deletePx = false) {
            this.ajaxLoading = true;

            this.valuationResult = false;
            this.mileage = 0;
            this.registrationYear = null;

            this.$store.commit('setPXMileage', 0);
            this.$store.commit('setPXRegistrationYear', 0);

            this.PXVrm.carInfo.capId = null;
            this.PXVrm.carInfo.model = null;
            this.PXVrm.carInfo.title = null;
            this.PXVrm.carInfo.vrm = null;
            this.PXVrm.carInfo.vrmInput = '';
            this.PXVrm.carInfo.registrationYear = null;
            this.PXVrm.carDetails.result = false;
            this.PXVrm.carDetails.vrmDisabled = false;
            this.PXVrm.carNotFound = false;
            this.PXVrm.carAlternatives = [];

            this.$store.commit('setPXVrmCarInfoCapId', null);
            this.$store.commit('setPXVrmCarInfoModel', null);
            this.$store.commit('setPXVrmCarInfoTitle', null);
            this.$store.commit('setPXVrmCarInfoVrm', null);
            this.$store.commit('setPXVrmCarInfoDerivative', null);
            this.$store.commit('setPXVrmCarInfoVrmInput', '');
            this.$store.commit('setPXVrmCarInfoRegistrationYear', 0);
            this.$store.commit('setPXVrmCarDetailsResult', false);
            this.$store.commit('setPXVrmCarDetailsVrmDisabled', false);
            this.$store.commit('setPXVrmCarNotFound', false);
            this.$store.commit('setPXVrmCarAlternatives', []);

            this.PXValuation.valuationResult = false;
            this.PXValuation.partExchangeValue = 0;
            this.PXValuation.valuationCompleted = false;
            this.PXValuation.outstandingFinance = 0;

            this.$store.commit('setPXValuationValuationResult', false);
            this.$store.commit('setPXValuationPartExchangeValue', 0);
            this.$store.commit('setPXValuationValuationCompleted', false);
            this.$store.commit('setPXValuationOutstandingFinance', 0);

            this.additionalInfo.forEach((checkbox) => {
                checkbox.checked = checkbox.default;
            });

            this.carConditions.forEach((condition, index) => {
                if (condition.is_default) {
                    this.activeCondition = condition.id;
                }
            });

            this.resetPartExchange(deletePx);

            this.$parent.title = this.translateString('Do you have an existing car to Trade-In?');

            if (closeAccordion) {
                this.$parent.show = false;
            }
            this.disableSelects = true;
        },

        softReset(deletePx = false) {
            this.resetFilters(false, deletePx);
            this.peId = null;
        },

        softResetFiltersFail() {
            this.ajaxLoading = false;
        },

        softResetFiltersSuccess() {
            this.ajaxLoading = false;
            this.$emit('PartExchange::softResetSuccess');
        },

        closeModal() {
            if (this.$parent.$els.closeTrigger) {
                this.$parent.$els.closeTrigger.click();
            }
        },

        updatePartExchange(data) {
            if (!this.canEdit) {
                var mySavedCar = this.$parent.$parent;

                mySavedCar.pxValue = this.PXValuation.partExchangeValue + parseFloat(this.PXValuation.outstandingFinance);
                mySavedCar.mileage = this.mileage;
                mySavedCar.registrationYear = this.registrationYear;
                mySavedCar.outstandingFinance = this.PXValuation.outstandingFinance;
                mySavedCar.expireDate = data.data.expire; // WIP
            }

            this.$dispatch('Main::forceUpdateFinanceQuote');
        },

        resetPartExchange(deletePx = false) {
            if (this.resetUrl) {
                this.$http({
                    url: this.resetUrl,
                    data: {
                        px_id: (deletePx) ? this.peId : 0
                    }
                }).then(this.softResetFiltersSuccess, this.softResetFiltersFail);
            }
        },

        setGetValuationStep() {
            this.$refs.partExchangeValuationResult.valuationResult = false;
            this.disableSelects = false;
            this.valuationResult = false;
        },

        closePartExchange() {
            if (this.PXVrm.carInfo.title && this.PXVrm.carInfo.vrm) {
                this.$dispatch('CheckoutAccordionGroup::nextStep', this.$parent.stepCode);
            }

            this.closeModal();
        },

        getStatusBar() {
            var statusBar = 'Add';
            var statusAction = 'add';
            if (this.PXVrm.carInfo.title && this.PXVrm.carInfo.vrm) {
                var titleValue = this.PXValuation.partExchangeValue - this.PXValuation.outstandingFinance;
                statusBar = `PLATE NUMBER: ${this.PXVrm.carInfo.vrm} - ${currencySymbol}${numeral(titleValue).format('0,0.00')}`;
                statusAction = 'edit';
            }

            return { message: statusBar, action: statusAction };
        },

        changeCondition(id) {
            if (this.activeCondition !== id) {
                this.activeCondition = id;
                this.setGetValuationStep();
            }
        }
    },

    ready() {
        if (this.savedPx && Object.keys(this.savedPx).length) {
            this.loadValuation(this.savedPx);
        }

        if (!this.canEdit) {
            this.$refs.partExchangeVrm.carNotFound = false;
            this.$refs.partExchangeVrm.carDetails.result = true;
            this.$refs.partExchangeVrm.carDetails.vrmDisabled = true;
            this.$refs.partExchangeValuationResult.valuationResult = false;
            this.disableSelects = false;
            this.valuationResult = false;
        }

        jQuery('.px-additional-info-block input').change(() => {
            this.setGetValuationStep();
        });

        jQuery('.px-mileage-block').on('change keyup paste', () => {
            this.setGetValuationStep();
        });

        var activeCondition;

        if (!this.activeCondition) {
            this.carConditions.forEach((condition) => {
                if (condition.is_default) {
                    activeCondition = condition.id;
                }
            });
        } else {
            activeCondition = this.activeCondition;
        }

        this.activeCondition = activeCondition;
    },

    components: {
        appTooltip,
        appRangeSlider,
        appPartExchangeVrm,
        appPartExchangeCustomCar
    }
};
