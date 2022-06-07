<template>
    <div class="row">
        <div class="col-12">
            <div class="estimated-value">
                <div class="estimated-value-info">
                    <p>{{ 'Your estimated trade-in value today' | translate }}</p>
                    <app-tooltip :tooltip-position="'bottom'">
                        <p class="icon-info" slot="tooltipElement"></p>
                        <div class="tooltip-valuation" slot="tooltipContent" v-html="estValueDisclaimer"></div>
                    </app-tooltip>
                </div>
                <div class="col-6">
                    <hr>
                    <p class="value dsp2-blue">
                        {{ partExchangeValue | numberFormat '0,0.00' true }}
                    </p>
                    <hr>
                </div>
                <div class="future-value col-12" v-if="getPxFutureValueEnabled">
                    <div v-html="getReplacedBlock"></div>
                    <hr>
                </div>
            </div>
        </div>
        <div class="col-12 px-settlement">
            <div class="px-popup-info-block">
                <h3 class="h3 px-step-title"> {{ 'Outstanding Finance' | translate }} </h3>
                <p>{{ 'If you are still paying off your trade-in vehicle, what is the amount required to settle your vehicle finance?' | translate }}</p>
            </div>
        </div>
        <div class="col-12 px-settlement-value">
            <div class="px-settlement-value-wrapper">
                <div class="px-settlement-checkboxes">
                    <template v-for="(index, amount) in settlementValueOptions">
                        <div>
                            <input type="radio"
                               name="settlement"
                               class="radio-checkbox"
                               :id="'settlement-'+index"
                               :value="amount.value"
                               v-model="selectedValue"
                               @click="changeOutstandingFinance(index) + getSettlementQuotes(index)"
                            >
                            <label :for="'settlement-'+index">
                                <span></span>
                                {{ amount.label | translate }}
                            </label>
                        </div>
                    </template>
                </div>
                <hr>
                <template v-if="isCapture">
                    <div class="px-outstanding">
                        <div class="px-outstanding-input">
                            <div>
                                <div class="col-6">
                                    <p>{{ 'Please enter your outstanding finance amount.' | translate }}</p>
                                </div>
                                <div class="col-6">
                                    <div class="input-label-wrapper">
                                        <input type="text"
                                           id="outstanding-amount"
                                           maxlength="14"
                                           v-model="manualOutstandingFinance | numberFormat '0,0.00' true"
                                           @focus="selectOutstandingFinance"
                                           @blur="deselectOutstandingFinance"
                                        >
                                        <label class="input-label" for="outstanding-amount">
                                            {{ 'Outstanding amount to be settled' | translate }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div>
                        <div class="auto-settlement">
                            <div v-if="customerIsLoggedIn && !isCapture" class="auto-settlement-wrapper">
                                <app-settlement
                                    :settlement-quotes="settlementQuotes"
                                    :current-px-cap-id="currentPxCapId"
                                    :current-px-plate-year="currentPxPlateYear"
                                    :settlement-quotes-error="settlementQuotesError"
                                    :settlement-terms-url="PX.settlementTermsUrl"
                                    :selected-settlement-cap-code="selectedSettlementCode"
                                ></app-settlement>
                            </div>
                            <div v-else class="auto-settlement-wrapper">
                                <p class="px-regular-heading settlment-option-description">
                                    {{ 'If your vehicle is financed through BMW Group Financial Services, you can retrieve your outstanding settlement value by signing into your BMW account.' | translate }}
                                </p>
                                <div class="px-regular-heading settlement-button-wrapper">
                                    <button class="button button-narrow dsp2-outline" @click="saveValuationAndRedirectToLogin()">
                                        {{ 'Sign in to my account' | translate }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <div class="px-outstanding">
                    <div class="col-12 px-outstanding-separator">
                        <hr>
                    </div>
                    <div class="px-outstanding-amount col-12">
                        <div class="col-5 col-md-6">
                            <p>{{ 'Your outstanding finance amount' | translate }}</p>
                            <hr>
                            <p class="value dsp2-blue">{{ outstandingFinance | numberFormat '0,0.00' true }}</p>
                            <hr>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <app-negative-equity-payment
                    v-ref:negative-equity
                    :px-outstanding-finance="outstandingFinance"
                    :px-part-exchange-value="partExchangeValue"
                    :checkout="checkout"
                    :my-account="myAccount"
                    :pdp="pdp"
                    :car-finder="carFinder"
                    :has-negative-equity="hasNegativeEquity"
                    :is-pay-in-full="isPayInFullGroup"
                    :finance-filter="financeFilter"
                    :finance-quote="financeQuote"
                    :reset-url="resetUrl"
                    :update-step-url="updateStepUrl"
                    :part-exchange-valuation-result="partExchangeValuationResult"
                    :part-exchange-filter="PX"
                    :accessed-from="accessedFrom"
                ></app-negative-equity-payment>
            </div>
        </div>
    </div>
</template>

<script>
    import PartExchangeValuation from 'dsp2/components/Shared/PartExchangeValuation';
    import numeral from 'numeral';
    import appTooltip from 'dsp2/components/Elements/Tooltip';
    import appSettlement from 'dsp2/components/AutoSettlement/Settlement';
    import appNegativeEquityPayment from 'dsp2/components/PartExchange/PartExchangeComponents/NegativeEquityPayment';
    import Constants from 'dsp2/components/Shared/Constants';

    export default Vue.extend({
        mixins: [PartExchangeValuation, Constants],

        props: {
            estValueDisclaimer: {
                required: true,
                type: String
            },

            settlementValueOptions: {
                required: false,
                type: Array,
                default: () => [
                    {
                        value: 'capture',
                        label: 'Capture my settlement value',
                    },

                    {
                        value: 'retrieve',
                        label: 'Retrieve my settlement value with BMW',
                    }
                ]
            },

            productId: {
                required: false,
                type: [Number, Boolean],
                default: false
            },

            leadTimePartExchangeValue: {
                required: false,
                type: Number,
                default: 0
            },

            accessedFrom: {
                required: false,
                type: String,
                default: ''
            },

            financeQuote: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            },

            settlementQuotesUrl: {
                required: false,
                type: String
            },

            valuationUrl: {
                required: false,
                type: String,
                default: ''
            },

            saveValuationUrl: {
                required: false,
                type: String,
                default: ''
            },

            customerAccountUrl: {
                required: false,
                type: String,
                default: ''
            },

            customerIsLoggedIn: {
                required: false,
                type: Boolean,
                default: false
            },

            checkoutAccordion: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            },

            stepCode: {
                required: false,
                type: String,
                default: ''
            },

            financeFilter: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            },

            checkout: {
                required: false,
                type: Boolean,
                default: false
            },

            pdp: {
                required: false,
                type: Boolean,
                default: false
            },

            myAccount: {
                required: false,
                type: Boolean,
                default: false
            },

            carFinder: {
                required: false,
                type: Boolean,
                default: false
            },

            partExchangeVrm: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            },

            pxCondition: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            },

            resetUrl: {
                required: false,
                type: String,
                default: ''
            },

            partExchangeValuationResult: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            },

            updateStepUrl: {
                required: false,
                type: String,
                default: ''
            }
        },

        data() {
            return {
                pxValue: 0,
                hasOutstandingFinanceFocus: false,
                negativeEquityPayment: false,
                settlementQuotes: [],
                currentPxCapId: '',
                currentPxPlateYear: '',
                showSettlementQuotesPopup: false,
                settlementQuotesError: '',
                noMatchesErrorHtml: '<div>' +
                    'We are unable to validate your BMW Group Financial Services customer information using the details' +
                    'you provided. Please contact us on' +
                    '<a href="tel:+27800600555">080 060 0555 </a> for further assistance.' +
                    '<div>',
                selectedValue: this.settlementValueOptions[0].value,
                manualOutstandingFinance: 0,
                selectedSettlementCode: ''
            }
        },

        computed: {
            ProductGrid() {
                return !this.financeQuote ? this.$root.$refs.productGrid : '';
            },

            getPxFutureValueEnabled() {
                return this.$store.state.general.pxFutureValueEnabled;
            },

            getReplacedBlock() {
                return this.$store.state.general.pxFutureValueBlock;
            },

            PXVrm() {
                return this.$store.state.general.PX.Vrm;
            },

            Valuation() {
                return this.$store.state.general.PX.Valuation;
            },

            isCapture() {
                return this.selectedValue === this.settlementValueOptions[0].value;
            },

            initialDeposit() {
                if (this.myAccount) {
                    return 0;
                } else {
                    return this.pdp
                        ? this.financeQuote.cashDeposit
                        : this.financeFilter.initialPaymentAmount;
                }
            }
        },

        methods: {
            getValuation() {
                EventsBus.$emit('PartExchangeFilter::ajaxLoading', true);
                const data = {
                    px_id: this.PX.peId,
                    mileage: this.PX.mileage,
                    carCondition: this.PX.activeCondition,
                    additionalInfo: this.PX.additionalInfo
                }

                if (this.pdp || this.checkout) {
                    data.productId = this.productId;
                }

                const promise = this.$http({
                    url: this.valuationUrl,
                    data
                });
                promise.then(this.getValuationSuccess, this.getValuationFail);

                return promise;
            },

            getValuationSuccess(valuation) {
                this.activateThirdStep();
                valuation = valuation.data;
                this.partExchangeValue = valuation.totals.total;
                this.tradeValueFound = this.partExchangeValue === 0;
                this.$store.commit('setPXValuationPartExchangeValue', valuation.totals.total);
                this.$store.commit('setPXValuationValuationResult', true);
                this.$store.commit('setPXVrmCarInfoModel', valuation.cap.model);
                this.$store.commit('setPXVrmCarInfoVrm', valuation.vrm);
                this.$store.commit('setPXVrmCarInfoVrmInput', valuation.vrm);
                this.$store.commit('setPXVrmCarInfoCapId', valuation.cap.capid);
                this.$store.commit('setPXVrmCarInfoTitle', valuation.cap.derivative_spec);
                this.$store.commit('setPXVrmCarInfoDerivative', valuation.cap.derivative);
                this.$store.commit('setPXMileage', valuation.mileage);

                if (this.PX.currentStep < 2 && !this.PX.isExpired) {
                    this.PX.currentStep++;
                }

                if (this.pdp || this.myAccount || this.checkout) {
                    this.pxCondition.valuationResult = true;
                    this.PX.valuationResultConditions = true;
                }

                if (this.checkout) {
                    this.leadTimePartExchangeValue = valuation.updated_part_exchange_value;
                    this.PX.valuationResultConditions = true;
                }

                if (valuation.future_info_block) {
                    this.$store.commit('setPxFutureValueBlock', valuation.future_info_block.replace(/&#39;/g, '\''));
                    this.$store.commit('setPxFutureValueEnabled', valuation.future_value_enabled);
                }
            },

            saveValuationSuccess(save) {
                save = save.data;
                this.PX.peId = save.px_id;

                this.PXVrm.carDetails.result = true;
                this.valuationResult = true;
                this.PX.valuationResult = true;
                this.valuationCompleted = true;
                this.$store.commit('setPXValuationValuationResult', true);
                this.$store.commit('setPXValuationValuationCompleted', true);

                if (this.carFinder) {
                    this.financeFilter.filterCollection();
                }

                this.PX.isExpired = false;

                if ((this.PX.canEdit && this.PX.peId) || this.financeQuote) {
                    if (this.pdp) {
                        this.financeQuote.removeOverlayParamFromURL(this.OVERLAYSEARCHPARAMS.TRADEIN);
                        window.location.reload();
                    } else if (this.checkout) {
                        if (!Array.isArray(this.PX.savedPx) && typeof this.PX.savedPx === 'object') {
                            for (const prop of Object.getOwnPropertyNames(this.PX.savedPx)) {
                                delete this.PX.savedPx[prop];
                            }
                        }

                        this.PX.savedPx = {
                            vrm: this.partExchangeVrm.carInfo.vrm
                        };
                        EventsBus.$emit('PartExchangeFilter::ajaxLoading', false);
                        this.$dispatch('CheckoutAccordionGroup::nextStep', this.stepCode);
                        this.$root.$broadcast('Main::progressUpdateFinanceQuote'); // recalculate quote
                    } else {
                        // Landing/Results/My Account
                        window.location.reload();
                    }
                } else {
                    this.$dispatch('success');
                }
            },

            saveValuation() {
                EventsBus.$emit('PartExchangeFilter::ajaxLoading', true);

                if (this.checkout) {
                    const checkoutStep = (typeof this.checkoutAccordion.getNextStepIndex === 'function')
                        ? this.checkoutAccordion.getNextStepIndex()
                        : '';

                    this.$http({
                        url: this.saveValuationUrl,
                        data: {
                            vrm: this.partExchangeVrm.carInfo.vrm,
                            cap_id: parseInt(this.partExchangeVrm.carInfo.capId),
                            car_model: this.partExchangeVrm.carInfo.model,
                            car_mileage: this.PX.mileage,
                            car_condition: this.PX.activeCondition,
                            checkboxes: this.checkboxesValues,
                            outstanding_finance: this.outstandingFinance,
                            outstanding_finance_settlement: this.Valuation.outstandingFinanceSettlement,
                            part_exchange_value: this.partExchangeValue,
                            browser: window.navigator.userAgent,
                            px_id: this.PX.peId,
                            step: checkoutStep,
                            updated_part_exchange_value: this.leadTimePartExchangeValue
                        }
                    }).then(this.saveValuationSuccess, this.saveValuationFail);
                } else {
                    const promise = this.$http({
                        url: this.saveValuationUrl,
                        data: {
                            vrm: this.partExchangeVrm.carInfo.vrm,
                            cap_id: parseInt(this.partExchangeVrm.carInfo.capId),
                            car_model: this.partExchangeVrm.carInfo.model,
                            car_mileage: this.PX.mileage,
                            car_condition: this.PX.activeCondition,
                            checkboxes: this.checkboxesValues,
                            outstanding_finance: this.outstandingFinance,
                            part_exchange_value: this.partExchangeValue,
                            browser: window.navigator.userAgent,
                            px_id: this.PX.peId,
                            outstanding_finance_settlement: this.Valuation.outstandingFinanceSettlement,
                            manual_outstanding_finance: this.manualOutstandingFinance,
                            selected_settlement_option: this.selectedValue,
                            selected_settlement_quote_id: this.selectedSettlementCode
                        }
                    });

                    promise.then(this.saveValuationSuccess, this.saveValuationFail);

                    return promise;
                }
            },

            skipPXWithOutPX() {
                EventsBus.$emit('PartExchangeFilter::PXWithOutPX', null);
            },

            selectOutstandingFinance() {
                this.hasOutstandingFinanceFocus = true;
            },

            deselectOutstandingFinance() {
                this.hasOutstandingFinanceFocus = false;
            },

            saveValuationAndRedirectToLogin() {
                EventsBus.$emit('PartExchangeFilter::ajaxLoading', true);
                sessionStorage.setItem('trade_in_overlay', true);

                const promise = this.$http({
                    url: this.saveValuationUrl,
                    data: {
                        vrm: this.partExchangeVrm.carInfo.vrm,
                        cap_id: parseInt(this.partExchangeVrm.carInfo.capId),
                        car_model: this.partExchangeVrm.carInfo.model,
                        car_mileage: this.PX.mileage,
                        car_condition: this.PX.activeCondition,
                        checkboxes: this.checkboxesValues,
                        outstanding_finance: this.Valuation.outstandingFinance,
                        part_exchange_value: this.partExchangeValue,
                        browser: window.navigator.userAgent,
                        px_id: this.PX.peId,
                        outstanding_finance_settlement: this.Valuation.outstandingFinanceSettlement,
                        manual_outstanding_finance: this.manualOutstandingFinance,
                        selected_settlement_option: this.selectedValue
                    }
                });

                promise.then(this.redirectToLogin, this.saveValuationFail);

                return promise;
            },

            redirectToLogin() {
                window.location.href = this.customerAccountUrl;
            },

            changeOutstandingFinance(index) {
                this.selectedValue = this.settlementValueOptions[index].value

                if (this.isCapture && this.manualOutstandingFinance) {
                    this.outstandingFinance = this.manualOutstandingFinance;
                }
            },

            getSettlementQuotes() {
                if (!this.isCapture && this.customerIsLoggedIn) {
                    EventsBus.$emit('PartExchangeFilter::ajaxLoading', true);

                    const promise = this.$http({
                        url: this.settlementQuotesUrl
                    });

                    promise.then(this.getSettlementQuotesSuccess, this.getSettlementQuotesFail);

                    return promise;
                }
            },

            getSettlementQuotesSuccess(response) {
                this.settlementQuotes = [];

                if (response.data.success) {
                    this.settlementQuotes = response.data.settlementQuotes;
                    this.currentPxCapId = response.data.currentPxCapId;
                    this.currentPxPlateYear = response.data.currentPxPlateYear;
                    this.$broadcast('SettlementQuotes::SettlementQuotesSuccess', this.settlementQuotes);
                } else {
                    if (response.data.errorCode === 'no_matches') {
                        this.settlementQuotesError = this.noMatchesErrorHtml;
                    } else {
                        this.settlementQuotesError = response.data.error;
                    }
                }
                EventsBus.$emit('PartExchangeFilter::ajaxLoading', false);
            },

            getSettlementQuotesFail(response) {
                this.$broadcast('SettlementQuotes::SettlementQuotesError', response.data.error);
                EventsBus.$emit('PartExchangeFilter::ajaxLoading', false);
                alert(response.data.error);
            },

            clearOutstandingFinanceData() {
                this.manualOutstandingFinance = 0;
                this.selectedValue = this.settlementValueOptions[0].value;
                this.selectedSettlementCode = '';
            }
        },

        events: {
            'SettlementQuotePopup::QuoteSelected'(data) {
                this.outstandingFinance = data.selectedSettlement;
                this.selectedSettlementCode = data.selectedSettlementCode;
            }
        },

        watch: {
            'manualOutstandingFinance'(newValue) {
                if (this.isCapture) {
                    this.outstandingFinance = newValue;
                }
            },

            'partExchangeValue'(newValue) {
                this.pxValue = newValue;

                if (newValue === 0) {
                    this.tradeValueFound = true;
                }
            },

            'outstandingFinance'(newValue) {
                if (!this.myAccount) {
                    this.hasNegativeEquity = newValue > (
                        this.partExchangeValue + this.initialDeposit
                    );
                }

                if (!isNaN(newValue) && this.isCapture && newValue !== this.manualOutstandingFinance) {
                    this.manualOutstandingFinance = newValue;
                }

                EventsBus.$emit('PartExchangeFilter::negativeEquity', this.hasNegativeEquity);
            }
        },

        created() {
            window.EventsBus.$on('PartExchangeValuation::saveValuation', () => {
                this.saveValuation();
            });
        },

        ready() {
            const hasVrm = this.PX.tempPx ? (!!this.PX.tempPx.vrm && !!this.PX.savedPx.vrm) : false;

            if (hasVrm && typeof this.$store.state.general.pxFutureValueEnabled === 'undefined') {
                this.getValuation();
            }
        },

        components: {
            appTooltip,
            numeral,
            appSettlement,
            appNegativeEquityPayment
        }
    });
</script>
