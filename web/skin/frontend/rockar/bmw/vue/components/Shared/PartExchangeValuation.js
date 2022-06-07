import NumbericInputMixin from 'core/mixins/NumericInput';
import numeral from 'numeral';

export default {
    mixins: [NumbericInputMixin],

    data() {
        return {
            outstandingFinance: 0,
            valuationResult: false,
            partExchangeValue: 0,
            valuationCompleted: false,
            tradeinShortfallOnecashPayment: 2 // Settle trade-in shortfall in a single lump sum payment.
        };
    },

    computed: {
        PX() {
            return this.$parent;
        },

        isValuationValid() {
            return true;
        },

        checkboxesValues() {
            var list = [];

            this.PX.additionalInfo.forEach((ai) => {
                list.push(ai.id);
            });

            return list;
        },

        titleValue() {
            var titleValue = this.partExchangeValue - this.outstandingFinance;
            return currencySymbol + numeral(titleValue).format('0,0');
        },

        computedPartExchangeValue() {
            if (this.productId && this.leadTimePartExchangeValue !== false && !isNaN(this.leadTimePartExchangeValue)) {
                return this.leadTimePartExchangeValue;
            } else {
                return this.partExchangeValue;
            }
        },

        hasNegativeEquity() {
            const cashDeposit = this.$root.$refs.financeQuote ? this.$root.$refs.financeQuote.cashDeposit : 0;

            return this.outstandingFinance > (this.computedPartExchangeValue + cashDeposit);
        },

        outstandingFinanceSettlementOptions() {
            return [
                {
                    id: 1,
                    title: 'Add to monthly payment'
                },
                {
                    id: 2,
                    title: 'Once-off payment'
                }
            ];
        },

        financeActivePaymentGroupId() {
            return this.$store.state.finance.financeGroupId;
        },

        productGridCashPayment() {
            return this.$root.$refs.productGrid ? this.$root.$refs.productGrid.payInFullPayment : null;
        },

        financeQuoteCashPayment() {
            return this.$root.$refs.financeQuote ? this.$root.$refs.financeQuote.payInFullPayment : null;
        },

        payInFullPayment() {
            return this.productGridCashPayment || this.financeQuoteCashPayment || [];
        },

        isPayInFullGroup() {
            return this.payInFullPayment.find(payment => payment.group_id === this.financeActivePaymentGroupId)
                !== undefined;
        }
    },

    filters: {
        numberInputFormat: {
            read(number) {
                return currencySymbol + numeral(number).format('0,0.00');
            },

            write(number) {
                return numeral(number).value().toFixed(2);
            }
        },

        numberOutstandingFinanceFormat: {
            read(number) {
                if (isNaN(number)) {
                    number = 0;
                }

                if (this.hasOutstandingFinanceFocus) {
                    return number !== 0 ? numeral(number).format('0,0.00') : '';
                } else {
                    return currencySymbol + numeral(number).format('0,0.00');
                }
            },
            write(number) {
                number = numeral(number.toString().replace(/[^0-9^.]/g, '')).value();

                if (number < 0) {
                    number = 0;
                }

                return numeral(number).value().toFixed(2);
            }
        }
    },

    methods: {
        changeNegativeEquityPayment(data) {
            this.PX.activeFinanceSettlementCondition = data.id;
        },

        getPxOutstandingFinanceSettlement() {
            const general = this.$store.state.general;

            return (general && general.PX && general.PX.Valuation
                && general.PX.Valuation.outstandingFinanceSettlement)
                || this.PX.activeFinanceSettlementCondition;
        },

        getValuation() {
            this.PX.ajaxLoading = true;
            this.$http({
                url: this.PX.valuationUrl,
                data: {
                    px_id: this.PX.peId,
                    mileage: this.PX.mileage,
                    carCondition: this.PX.activeCondition,
                    additionalInfo: this.PX.additionalInfo
                }
            }).then(this.getValuationSuccess, this.getValuationFail);
        },

        getValuationSuccess(valuation) {
            this.activateThirdStep();
            valuation = valuation.data;
            this.partExchangeValue = valuation.totals.total;
            this.$store.commit('setPXValuationPartExchangeValue', valuation.totals.total);
            this.$store.commit('setPXValuationValuationResult', true);
            this.$store.commit('setPXVrmCarInfoModel', valuation.cap.model);
            this.$store.commit('setPXVrmCarInfoVrm', valuation.vrm);
            this.$store.commit('setPXVrmCarInfoVrmInput', valuation.vrm);
            this.$store.commit('setPXVrmCarInfoCapId', valuation.cap.capid);
            this.$store.commit('setPXVrmCarInfoTitle', valuation.cap.derivative_spec);
            this.$store.commit('setPXVrmCarInfoDerivative', valuation.cap.derivative);
            this.$store.commit('setPXMileage', valuation.mileage);
        },

        getValuationFail(error) {
            this.PX.ajaxLoading = false;
            console.error(error.statusText);
        },

        saveValuation() {
            this.PX.ajaxLoading = true;
            if (this.isPayInFullGroup) {
                this.changeNegativeEquityPayment(this.tradeinShortfallOnecashPayment);
            }
            this.$http({
                url: this.PX.saveValuationUrl,
                data: {
                    vrm: this.PX.$refs.partExchangeVrm.carInfo.vrm || 0,
                    cap_id: parseInt(this.PX.$refs.partExchangeVrm.carInfo.capId),
                    car_model: this.PX.$refs.partExchangeVrm.carInfo.model,
                    car_mileage: this.PX.mileage,
                    car_condition: this.PX.activeCondition,
                    checkboxes: this.checkboxesValues,
                    outstanding_finance: this.outstandingFinance,
                    part_exchange_value: this.partExchangeValue,
                    browser: window.navigator.userAgent,
                    px_id: this.PX.peId,
                    outstanding_finance_settlement: this.PX.outstandingFinanceSettlement || this.PX.activeFinanceSettlementCondition
                }
            }).then(this.saveValuationSuccess, this.saveValuationFail);
        },

        saveValuationFail(error) {
            this.PX.ajaxLoading = false;
            this.$store.commit('setNotificationMessage', { message: error.data.errorMessage, type: 'error' });
        },

        savePartExchangeToSession() {
            this.PX.ajaxLoading = true;
            this.$http({
                url: this.PX.saveToSessionUrl,
                data: {
                    outstanding_finance: this.outstandingFinance,
                    part_exchange_value: this.partExchangeValue
                }
            }).then(this.savePartExchangeToSessionSuccess, this.savePartExchangeToSessionFail);
        },

        savePartExchangeToSessionFail(error) {
            this.PX.ajaxLoading = false;
            this.$store.commit('setNotificationMessage', { message: error.data.errorMessage, type: 'error' });
        },

        savePartExchangeToSessionSuccess() {
            this.PX.ajaxLoading = false;
        },

        closePartExchange() {
            this.PX.closePartExchange();
            this.valuationCompleted = true;
        },

        activateThirdStep() {
            if (this.PX) {
                this.PX.ajaxLoading = false;
                this.valuationResult = true;
                this.PX.disableSelects = false;
                this.PX.valuationResult = true;
                this.valuationCompleted = false;
            }
        }
    },

    events: {
        'PartExchange::updateActiveFinanceSettlementCondition'(newValue) {
            this.$store.commit('setPXOutstandingFinanceSettlement', newValue);
            this.PX.activeFinanceSettlementCondition = newValue;
        }
    },

    created() {
        EventsBus.$on('PartExchange::updateActiveFinanceSettlementCondition', (val) => {
            this.$dispatch('PartExchange::updateActiveFinanceSettlementCondition', val);
        });
    },

    ready() {
        if (this.isPayInFullGroup) {
            this.changeNegativeEquityPayment(this.tradeinShortfallOnecashPayment);
        }

        jQuery('.px-outstanding-finance input').on('change keyup paste', () => {
            if (!this.isPayInFullGroup) {
                this.activateThirdStep();
            }
        });
    }
};
