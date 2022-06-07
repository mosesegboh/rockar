<template>
    <div class="px-shortfall-wrapper" v-if="hasNegativeEquity">
        <div class="col-12">
            <h3 class="h3 px-step-title">{{ 'Trade-in shortfall' | translate }}</h3>
        </div>
        <div class="px-shortfall col-12">
            <div class="col-12 px-outstanding-amount">
                <div class="col-6">
                    <div class="px-shortfall-value">
                        <p>{{ 'Trade-in shortfall' | translate }}</p>
                        <app-tooltip :tooltip-position="'bottom'" v-if="!isPayInFull">
                            <p class="icon-info" slot="tooltipElement"></p>
                            <div slot="tooltipContent" >
                                <p>{{ 'Based on the the information provided, you are required to settle the net amount due on your Trade-in vehicle. This is the difference between the trade-in value and the finance amount still owing on the vehicle. You can continue with your journey by selecting one of the three options below.' | translate }}</p>
                            </div>
                        </app-tooltip>
                        <app-tooltip :tooltip-position="'bottom'" v-if="isPayInFull">
                            <p class="icon-info" slot="tooltipElement"></p>
                            <div slot="tooltipContent">
                                <p>{{ 'Based on the the information provided, you are required to settle the net amount due on your Trade-in vehicle. This is the difference between the trade-in value and the finance amount still owing on the vehicle. You can continue with your journey, your trade-in shortfall will be settled in a single lump sum payment.' | translate }}</p>
                            </div>
                        </app-tooltip>
                    </div>
                    <hr>
                    <p class="value dsp2-blue">{{ computeNegativeEquity | numberFormat '0,0.00' true }}</p>
                    <hr>
                </div>
            </div>
            <div class="col-12 px-shortfall-payments" v-if="!isPayInFull">
                <p class="px-shortfall-mobile">{{ 'Please select one of the options below:' | translate }}</p>
                <div class="col-4 col-md-12">
                    <div class="row">
                        <div class="col-12">
                            <h3 class="h3">01</h3>
                            <p>{{ 'Pay-off your trade-in shortfall on a monthly basis.' | translate }}</p>
                        </div>
                        <div class="col-12">
                            <button class="button dsp2-outline"
                                @click="settleOutstandingFinance(this.SHORTFALL.TRADEIN_SHORTFALL_MONTHLY_PAYMENT)">
                                {{ 'Add to monthly payment' | translate }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-4 col-md-12">
                    <div class="row">
                        <div class="col-12">
                            <h3 class="h3">02</h3>
                            <p>{{ 'Settle your trade-in shortfall in a single lump sum payment.' | translate }}</p>
                        </div>
                        <div class="col-12">
                            <button class="button dsp2-outline" @click="settleOutstandingFinance(this.SHORTFALL.TRADEIN_SHORTFALL_ONE_CASH_PAYMENT)">
                                {{ 'Once-off payment' | translate }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-4 col-md-12">
                    <div class="row">
                        <div class="col-12">
                            <h3 class="h3">03</h3>
                            <p>{{ 'Keep your existing vehicle or trade-in your vehicle privately.' | translate }}</p>
                        </div>
                        <div class="col-12">
                            <button class="button dsp2-outline" @click="settleOutstandingFinance(this.SHORTFALL.TRADEIN_SHORTFALL_NONE, false)">
                                {{ 'Remove trade-in' | translate }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="px-shortfall-mobile">
        </div>
    </div>
</template>

<script>
    import appTooltip from 'dsp2/components/Elements/Tooltip';
    import numeral from 'numeral';
    import Constants from 'dsp2/components/Shared/Constants';

    export default Vue.extend({
        mixins: [Constants],

        data() {
            return {
                negativeEquity: 0,
                outstandingFinanceSettlement: 0,
                outstandingFinance: this.pxOutstandingFinance,
                partExchangeValue: this.pxPartExchangeValue,
                payInFull: false
            }
        },

        props: {
            pxOutstandingFinance: {
                required: true,
                type: Number
            },

            pxPartExchangeValue: {
                required: true,
                type: Number
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

            hasNegativeEquity: {
                required: false,
                type: Boolean,
                default: false
            },

            isPayInFull: {
                required: false,
                type: Boolean,
                default: false
            },

            financeQuote: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            },

            financeFilter: {
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

            updateStepUrl: {
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

            partExchangeFilter: {
                required: false,
                type: Object,
                default() {
                    return {};
                }
            },

            accessedFrom: {
                required: true,
                type: String
            }
        },

        computed: {
            computeNegativeEquity() {
                this.negativeEquity = 0;

                if (this.hasNegativeEquity) {
                    this.negativeEquity = this.pxPartExchangeValue - this.pxOutstandingFinance;
                    this.negativeEquity += this.payInFull ? 0 : this.initialDeposit;
                }

                if (this.isPayInFull) {
                    if (this.hasNegativeEquity) {
                        this.settleOutstandingFinance(this.SHORTFALL.TRADEIN_SHORTFALL_ONE_CASH_PAYMENT, false);
                    } else {
                        this.settleOutstandingFinance(this.SHORTFALL.TRADEIN_SHORTFALL_NONE, false);
                    }
                }

                return this.negativeEquity;
            },

            initialDeposit() {
                return this.pdp || this.checkout
                    ? this.financeQuote.cashDeposit
                    : this.financeFilter.initialPaymentAmount;
            }
        },

        methods: {
            settleOutstandingFinance(settlementChoice = 0, finishTradeIn = true) {
                this.$store.commit('setPXOutstandingFinanceSettlement', settlementChoice);
                this.$store.commit('setPXValuationOutstandingFinance', this.pxOutstandingFinance);

                /**
                 * Trade-in removed from Valuation step (shortfall)
                 */
                if (settlementChoice === this.SHORTFALL.TRADEIN_SHORTFALL_NONE) {
                    if (this.partExchangeFilter.peId) {
                        if (this.myAccount) {
                            EventsBus.$emit('PartExchangeFilter::ajaxLoading', true);
                        }

                        this.removePX();

                        if (this.carFinder) {
                            this.resetPxData();
                            this.flipModals();
                        }
                    } else {
                        this.resetPxData();

                        switch (this.accessedFrom) {
                            case this.PXACCESSPOINTS.CARFINDER:
                                this.removePX();
                                break;
                            case this.PXACCESSPOINTS.PDP:
                                this.financeQuote.removeOverlayParamFromURL(this.OVERLAYSEARCHPARAMS.TRADEIN);
                                this.financeQuote.editPartExchange = false;
                                break;
                            case this.PXACCESSPOINTS.MYACCOUNT:
                                this.$root.$refs.tradeInMyAccountModal.show = false;
                                break;
                            default:
                                break;
                        }
                    }
                }

                if (finishTradeIn) {
                    EventsBus.$emit('PartExchangeValuation::saveValuation');
                }
            },

            removePX() {
                if (!this.myAccount) {
                    this.$dispatch('PartExchangeFilter::ajaxLoading', true);
                }

                this.$http({
                    url: this.resetUrl,
                    method: 'POST',
                    emulateJSON: true,
                    data: {
                        px_id: this.partExchangeFilter.peId
                    }
                }).then(this.removePXSuccess, this.removePXFail);
            },

            removePXSuccess() {
                this.partExchangeFilter.isPxDeleteAction = true;
                this.partExchangeFilter.peId = '';

                switch (this.accessedFrom) {
                    case this.PXACCESSPOINTS.CARFINDER:
                        this.$dispatch('Main::resetPX', true);
                        this.flipModals();
                        this.financeFilter.removePXSuccess();
                        break;
                    case this.PXACCESSPOINTS.PDP:
                        this.financeQuote.removeOverlayParamFromURL(this.OVERLAYSEARCHPARAMS.TRADEIN);
                        this.financeQuote.editPartExchange = false;
                        break;
                    case this.PXACCESSPOINTS.CHECKOUT:
                        this.financeQuote.editPartExchange = false;
                        break;
                    case this.PXACCESSPOINTS.MYACCOUNT:
                        this.$root.$refs.tradeInMyAccountModal.show = false;
                        break;
                    default:
                        break;
                }

                if (!this.myAccount) {
                    this.$dispatch('PartExchangeFilter::ajaxLoading', false);
                }

                this.resetPxData();
            },

            removePXFail(error) {
                this.$dispatch('PartExchangeFilter::ajaxLoading', false);
                console.error('My Current Cars:', error);
            },

            resetPxData() {
                this.updatePxStateInSession(true);
            },

            updatePxStateInSession(value, url = false) {
                if (!url) {
                    url = this.updateStepUrl;
                }

                if (url) {
                    this.ajaxLoading = true;
                    const promise = this.$http({
                        url,
                        data: {
                            currentState: value
                        }
                    });

                    promise.then(this.updatePxStateInSessionSuccess, this.updatePxStateInSessionFail);

                    return promise;
                }
            },

            updatePxStateInSessionSuccess() {
                this.partExchangeValuationResult.outstandingFinance = 0;
                window.EventsBus.$emit('PartExchange::resetPxData');

                switch (this.accessedFrom) {
                    case this.PXACCESSPOINTS.CHECKOUT:
                        this.$dispatch('Main::progressUpdateFinanceQuote'); // Recalculate quote
                        break;
                    case this.PXACCESSPOINTS.PDP:
                        this.$dispatch('Quote::ResetPDPTradeIn'); // Recalculate quote
                        break;
                    case this.PXACCESSPOINTS.CARFINDER:
                        window.EventsBus.$emit('PartExchange::removedPx');
                        break;
                    default:
                        break;
                }
            },

            updatePxStateInSessionFail(error) {
                this.$dispatch('PartExchangeFilter::ajaxLoading', false);
                console.error('Current PX state:', error);
            },

            flipModals() {
                this.financeFilter.flipModals();
            }
        },

        ready() {
            if (this.myAccount) {
                this.payInFull = true;
            }
        },

        components: {
            appTooltip,
            numeral
        }
    })
</script>
