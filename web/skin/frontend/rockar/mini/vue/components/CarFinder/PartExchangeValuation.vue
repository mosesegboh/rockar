<template>
    <div class="row valuation-summary">
        <div class="col-12 text-center">
            <p class="px-regular-heading">{{ '*Estimated trade-in value:' | translate }}</p>
            <p class="final-valuation">
                {{ partExchangeValue | numberFormat '0,00.00' true }}
            </p>
            <p class="px-regular-heading">{{ 'Please note: This figure is an estimate based on the information you\'ve provided and the current value of your vehicle. The figure is subject to an onsite vehicle inspection which is mandatory when trading in your vehicle. The figure may vary as time passes or if there are any changes in your vehicle condition.' | translate }}</p>
            <div class="future-value-container" v-if="getPxFutureValueEnabled">
                <div class="future-value">
                    <hr>
                    <p>{{{ getReplacedBlock }}}</p>
                    <hr>
                </div>
                <app-more-info class="future-value show-more" :disable="disableMoreInfo">
                    <div slot="header-closed">{{ 'Show future value' | translate }}</div>
                    <div slot="header-opened">{{ 'Hide future value' | translate }}</div>
                    <p slot="content">{{{ getReplacedBlock }}}</p>
                </app-more-info>
            </div>
        </div>
        <div class="outstanding-finance">
            <div class="col-12 text-center">
                <div class="row">
                    <p id="financeSettlement" class="px-regular-heading">{{ 'Outstanding finance to be settled:' | translate }}</p>
                    <div class="info-label-wrap">
                        <app-tooltip :tooltip-position="'top-left'" :tooltip-width="400">
                            <span class="action-badge info-small" slot="tooltipElement"></span>

                            <div slot="tooltipContent">
                                <p>If you are still paying off your vehicle, what is the amount required to settle your vehicle finance? If your vehicle is fully paid off, please leave it blank.</p>
                            </div>
                        </app-tooltip>
                    </div>
                </div>
            </div>
            <div class="input-wrapper">
                <input type="text" class="active px-input keyboard-numbers"
                       v-model="outstandingFinance | numberOutstandingFinanceFormat"
                       placeholder=""
                       @focus="selectOutstandingFinance"
                       @blur="deselectOutstandingFinance"/>
            </div>
        </div>

        <div class="auto-settlement">
            <div v-if="PX.customerIsLoggedIn">
                <p class="px-regular-heading">
                    {{ 'View your Settlement Value' | translate }}
                </p>
                <p>
                    {{ 'If you are an existing customer with a BMW Group Financial Services vehicle to trade in, you can retrieve your outstanding settlement value using the option below:' | translate }}
                </p>
                <div class="px-regular-heading">
                    <button class="button button-narrow" @click="getSettlementQuotes()">
                        {{ 'View my settlement' | translate }}
                    </button>
                </div>
            </div>
            <div v-else>
                <p class="px-regular-heading">
                    {{ 'Sign in to view your Settlement Value' | translate }}
                </p>
                <p class="px-regular-heading">
                    {{ 'If your vehicle is financed through BMW Group Financial Services, you can retrieve your outstanding settlement value using the option below:' | translate }}
                </p>
                <p class="px-regular-heading">
                    <a href="javascript:void(0);" @click="saveValuationAndRedirectToLogin()">> {{ 'Sign in to my account' | translate }}</a>
                </p>
            </div>
        </div>

        <app-modal :show.sync="tradeValueFound" :show-close="false" class-name="no-value-popup">
            <div slot="content">
                <p class="modal-header">
                    <span>{{ 'We are unable to give you an estimated trade-in value online.' | translate }}</span>
                </p>
                <div class="main-content">
                    <p>
                        If you would like to include a trade-in with your quote,
                        you may add it at a later stage by visiting your preferred MINI Retailer to get a valuation of your vehicle.
                        To continue please skip this step.
                    </p>
                </div>
                <div class="row align-right">
                    <button class="button button-narrow popup-continue" @click="skipPXWithOutPX()">
                        {{ 'Add trade-in later' | translate }}
                    </button>
                </div>
            </div>
        </app-modal>

        <div class="skip-px-container mobile-only text-center">
            <hr>
            <div class="skip-px-info">
                <strong>{{ 'If you\'re not happy with your trade-in value:' | translate }}</strong>
            </div>
            <button class="button button-wide long-button next-step button-empty continue-px without-px mobile-tablet-only"
                    @click="skipPXWithOutPX">
                {{ 'Continue without trade-in' | translate }}
            </button>
        </div>

        <app-modal class-name="settlement-quote-popup" :show="showSettlementQuotesPopup" :show-close="true">
            <div slot="content">
                <app-settlement-popup
                    :settlement-quotes="settlementQuotes"
                    :current-px-cap-id="currentPxCapId"
                    :current-px-plate-year="currentPxPlateYear"
                    :settlement-quotes-error="settlementQuotesError"
                    :settlement-terms-url="PX.settlementTermsUrl"
                ></app-settlement-popup>
            </div>
        </app-modal>
    </div>

    <app-negative-equity-payment></app-negative-equity-payment>
</template>

<script>
    import PartExchangeValuation from 'mini/components/Shared/PartExchangeValuation';
    import numeral from 'numeral';
    import appMoreInfo from 'core/components/Elements/MoreInfo';
    import appNegativeEquityPayment from 'mini/components/CarFinder/NegativeEquityPayment';
    import appTooltip from 'core/components/Elements/Tooltip';
    import appModal from 'core/components/Elements/Modal';
    import appSettlementPopup from 'mini/components/AutoSettlement/SettlementPopup';

    export default Vue.extend({
        mixins: [PartExchangeValuation],

        data() {
            return {
                pxValue: 0,
                disableMoreInfo: false,
                hasOutstandingFinanceFocus: false,
                negativeEquityPayment: false,
                settlementQuotes: [],
                currentPxCapId: '',
                currentPxPlateYear: '',
                showSettlementQuotesPopup: false,
                settlementQuotesError: '',
                noMatchesErrorHtml: '<div>We are unable to validate your BMW Group Financial Services customer information using the details you provided. Please contact us on <a href="tel:+27800600555">080 060 0555</a> for further assistance.<div>'
            }
        },

        computed: {
            ProductGrid() {
                return this.$root.$refs.productGrid;
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
            }
        },

        methods: {
            getValuation() {
                this.PX.ajaxLoading = true;

                const promise = this.$http({
                    url: this.PX.valuationUrl,
                    data: {
                        px_id: this.PX.peId,
                        mileage: this.PX.mileage,
                        carCondition: this.PX.activeCondition,
                        additionalInfo: this.PX.additionalInfo
                    }
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

                if (this.$parent.$parent.currentStep < 2 && this.$parent.stepTwoSubStep < 2) {
                    this.$parent.stepTwoSubStep++;
                }

                if (valuation.future_info_block) {
                    this.$store.commit('setPxFutureValueBlock', valuation.future_info_block.replace(/&#39;/g, '\''));
                    this.$store.commit('setPxFutureValueEnabled', valuation.future_value_enabled);
                }
            },

            saveValuationSuccess(save) {
                save = save.data;
                this.PX.peId = save.px_id;

                if (this.outstandingFinance > 0) {
                    this.PX.$parent.title = `After Ford settles your existing finance, you will have  ${this.titleValue} to put towards your new car`;
                } else {
                    this.PX.$parent.title = `Your current car is worth ${this.titleValue} as a trade-in`;
                }

                this.PXVrm.carDetails.result = true;
                this.PX.ajaxLoading = false;
                this.valuationResult = true;
                this.PX.valuationResult = true;
                this.valuationCompleted = true;
                this.$store.commit('setPXValuationValuationResult', true);
                this.$store.commit('setPXValuationValuationCompleted', true);

                if (this.$parent.$parent.currentStep < 2) {
                    this.$parent.$parent.currentStep++;
                }

                this.PX.closePartExchange();
                this.$root.$refs.financeFilter.filterCollection();
            },

            saveValuation() {
                this.PX.ajaxLoading = true;
                const promise = this.$http({
                    url: this.PX.saveValuationUrl,
                    data: {
                        vrm: this.PX.$refs.partExchangeVrm.carInfo.vrm,
                        cap_id: parseInt(this.PX.$refs.partExchangeVrm.carInfo.capId),
                        car_model: this.PX.$refs.partExchangeVrm.carInfo.model,
                        car_mileage: this.PX.mileage,
                        car_condition: this.PX.activeCondition,
                        checkboxes: this.checkboxesValues,
                        outstanding_finance: this.Valuation.outstandingFinance,
                        part_exchange_value: this.partExchangeValue,
                        browser: window.navigator.userAgent,
                        px_id: this.PX.peId,
                        outstanding_finance_settlement: this.Valuation.outstandingFinanceSettlement
                    }
                });

                promise.then(this.saveValuationSuccess, this.saveValuationFail);

                return promise;
            },

            savePartExchangeToSession() {
                this.PX.ajaxLoading = true;
                const promise = this.$http({
                    url: this.PX.saveToSessionUrl,
                    data: {
                        outstanding_finance: this.outstandingFinance,
                        part_exchange_value: this.partExchangeValue
                    }
                });
                promise.then(this.savePartExchangeToSessionSuccess, this.savePartExchangeToSessionFail);

                return promise;
            },

            continueWithout() {
                this.valuationResult = true;
                this.PX.valuationResult = true;
                this.PX.step = -1;
                this.PX.PXVrm.step = -1;
                this.step = -1;
                this.valuationCompleted = true;
                this.PX.$parent.title = "I'm not swapping a car";
                this.partExchangeValue = 0;
                this.closePartExchange();

                return this.savePartExchangeToSession();
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
                this.PX.ajaxLoading = true;

                const promise = this.$http({
                    url: this.PX.saveValuationUrl,
                    data: {
                        vrm: this.PX.$refs.partExchangeVrm.carInfo.vrm,
                        cap_id: parseInt(this.PX.$refs.partExchangeVrm.carInfo.capId),
                        car_model: this.PX.$refs.partExchangeVrm.carInfo.model,
                        car_mileage: this.PX.mileage,
                        car_condition: this.PX.activeCondition,
                        checkboxes: this.checkboxesValues,
                        outstanding_finance: this.Valuation.outstandingFinance,
                        part_exchange_value: this.partExchangeValue,
                        browser: window.navigator.userAgent,
                        px_id: this.PX.peId,
                        outstanding_finance_settlement: this.Valuation.outstandingFinanceSettlement
                    }
                });

                promise.then(this.redirectToLogin, this.saveValuationFail);

                return promise;
            },

            redirectToLogin() {
                window.location.href = this.PX.customerAccountUrl;
            },

            getSettlementQuotes() {
                this.PX.ajaxLoading = true;

                const promise = this.$http({
                    url: this.PX.settlementQuotesUrl
                });

                promise.then(this.getSettlementQuotesSuccess, this.getSettlementQuotesFail);

                return promise;
            },

            getSettlementQuotesSuccess(response) {
                this.settlementQuotes = [];

                if (response.data.success) {
                    this.settlementQuotes = response.data.settlementQuotes;
                    this.currentPxCapId = response.data.currentPxCapId;
                    this.currentPxPlateYear = response.data.currentPxPlateYear;
                    this.showSettlementQuotesPopup = true;
                    this.$broadcast('SettlementQuotes::SettlementQuotesSuccess', this.settlementQuotes);
                } else {
                    if (response.data.errorCode === 'no_matches') {
                        this.settlementQuotesError = this.noMatchesErrorHtml;
                    } else {
                        this.settlementQuotesError = response.data.error;
                    }
                    this.showSettlementQuotesPopup = true;
                }
                this.PX.ajaxLoading = false;
            },

            getSettlementQuotesFail(response) {
                this.$broadcast('SettlementQuotes::SettlementQuotesError', response.data.error);
                this.PX.ajaxLoading = false;
                alert(response.data.error);
            }
        },

        events: {
            'SettlementQuotePopup::QuoteSelected'(value) {
                this.outstandingFinance = value;
                this.showSettlementQuotesPopup = false;
            },

            'close-popup'() {
                this.showSettlementQuotesPopup = false;
            }
        },

        watch: {
            'partExchangeValue'(newValue) {
                this.pxValue = newValue;

                if (newValue === 0) {
                    this.tradeValueFound = true;
                }
            },
        },

        components: {
            appMoreInfo,
            appTooltip,
            appModal,
            numeral,
            appNegativeEquityPayment,
            appSettlementPopup
        }
    });
</script>
